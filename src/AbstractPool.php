<?php

namespace Amp\Sql;

use Amp\CallableMaker;
use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;
use function Amp\call;
use function Amp\coroutine;

abstract class AbstractPool implements Pool
{
    use CallableMaker;

    /** @var Connector */
    private $connector;

    /** @var ConnectionConfig */
    private $connectionConfig;

    /** @var int */
    private $maxConnections;

    /** @var \SplQueue */
    private $idle;

    /** @var \SplObjectStorage */
    private $connections;

    /** @var \Amp\Promise|null */
    private $promise;

    /** @var \Amp\Deferred|null */
    private $deferred;

    /** @var callable */
    private $prepare;

    /** @var int */
    private $pending = 0;

    /** @var int */
    private $idleTimeout = Pool::DEFAULT_IDLE_TIMEOUT;

    /** @var string */
    private $timeoutWatcher;

    /** @var bool */
    private $closed = false;

    /**
     * Create a default connector object based on the library of the extending class.
     *
     * @return Connector
     */
    abstract protected function createDefaultConnector(): Connector;

    /**
     * Creates a ResultSet of the appropriate type using the ResultSet object returned by the Link object and the
     * given release callable.
     *
     * @param ResultSet $resultSet
     * @param callable  $release
     *
     * @return ResultSet
     */
    abstract protected function createResultSet(ResultSet $resultSet, callable $release): ResultSet;

    /**
     * Creates a Statement of the appropriate type using the Statement object returned by the Link object and the
     * given release callable.
     *
     * @param Statement $statement
     * @param callable  $release
     *
     * @return Statement
     */
    abstract protected function createStatement(Statement $statement, callable $release): Statement;

    /**
     * @param Pool      $pool
     * @param Statement $statement
     * @param callable  $prepare
     *
     * @return PooledStatement
     */
    abstract protected function createPooledStatement(Pool $pool, Statement $statement, callable $prepare): PooledStatement;

    /**
     * Creates a Transaction of the appropriate type using the Transaction object returned by the Link object and the
     * given release callable.
     *
     * @param Transaction $transaction
     * @param callable    $release
     *
     * @return Transaction
     */
    abstract protected function createTransaction(Transaction $transaction, callable $release): Transaction;

    public function __construct(
        ConnectionConfig $config,
        int $maxConnections = Pool::DEFAULT_MAX_CONNECTIONS,
        Connector $connector = null
    ) {
        $this->connector = $connector ?? $this->createDefaultConnector();

        $this->connectionConfig = $config;

        $this->maxConnections = $maxConnections;
        if ($this->maxConnections < 1) {
            throw new \Error("Pool must contain at least one connection");
        }

        $this->connections = $connections = new \SplObjectStorage;
        $this->idle = $idle = new \SplQueue;
        $this->prepare = coroutine($this->callableFromInstanceMethod("prepareStatement"));

        $idleTimeout = &$this->idleTimeout;

        $this->timeoutWatcher = Loop::repeat(1000, static function () use (&$idleTimeout, $connections, $idle) {
            $now = \time();
            while (!$idle->isEmpty()) {
                $connection = $idle->bottom();
                \assert($connection instanceof Link);

                if ($connection->lastUsedAt() + $idleTimeout > $now) {
                    return;
                }

                // Close connection and remove it from the pool.
                $idle->shift();
                $connections->detach($connection);
                $connection->close();
            }
        });

        Loop::unreference($this->timeoutWatcher);
    }

    public function __destruct()
    {
        Loop::cancel($this->timeoutWatcher);
    }

    public function getIdleTimeout(): int
    {
        return $this->idleTimeout;
    }

    public function setIdleTimeout(int $timeout)
    {
        if ($timeout < 1) {
            throw new \Error("Timeout must be greater than or equal to 1");
        }

        $this->idleTimeout = $timeout;
    }

    public function lastUsedAt(): int
    {
        // Simple implementation... can be improved if needed.

        $time = 0;

        foreach ($this->connections as $connection) {
            \assert($connection instanceof Link);
            if (($lastUsedAt = $connection->lastUsedAt()) > $time) {
                $time = $lastUsedAt;
            }
        }

        return $time;
    }

    /**
     * @return bool
     */
    public function isAlive(): bool
    {
        return !$this->closed;
    }

    /**
     * Close all connections in the pool. No further queries may be made after a pool is closed.
     */
    public function close()
    {
        $this->closed = true;
        foreach ($this->connections as $connection) {
            $connection->close();
        }
        $this->idle = new \SplQueue;
        $this->connections = new \SplObjectStorage;
        $this->prepare = null;
    }

    /**
     * {@inheritdoc}
     */
    public function extractConnection(): Promise
    {
        return call(function () {
            $connection = yield from $this->pop();
            $this->connections->detach($connection);
            return $connection;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionCount(): int
    {
        return $this->connections->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdleConnectionCount(): int
    {
        return $this->idle->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }

    /**
     * @return \Generator
     *
     * @resolve Link
     *
     * @throws FailureException If creating a new connection fails.
     * @throws \Error If the pool has been closed.
     */
    protected function pop(): \Generator
    {
        if ($this->closed) {
            throw new \Error("The pool has been closed");
        }

        while ($this->promise !== null && $this->connections->count() + $this->pending >= $this->getMaxConnections()) {
            yield $this->promise; // Prevent simultaneous connection creation when connection count is at maximum - 1.
        }

        do {
            // While loop to ensure an idle connection is available after promises below are resolved.
            while ($this->idle->isEmpty()) {
                if ($this->connections->count() + $this->pending < $this->getMaxConnections()) {
                    // Max connection count has not been reached, so open another connection.
                    ++$this->pending;
                    try {
                        $connection = yield $this->connector->connect($this->connectionConfig);
                        if (!$connection instanceof Link) {
                            throw new \Error(\sprintf(
                                "%s::connect() must resolve to an instance of %s",
                                \get_class($this->connector),
                                Link::class
                            ));
                        }
                    } finally {
                        --$this->pending;
                    }

                    $this->connections->attach($connection);
                    return $connection;
                }

                // All possible connections busy, so wait until one becomes available.
                try {
                    $this->deferred = new Deferred;
                    // May be resolved with defunct connection, but that connection will not be added to $this->idle.
                    yield $this->promise = $this->deferred->promise();
                } finally {
                    $this->deferred = null;
                    $this->promise = null;
                }
            }

            $connection = $this->idle->shift();
            \assert($connection instanceof Link);

            if ($connection->isAlive()) {
                return $connection;
            }

            $this->connections->detach($connection);
        } while (!$this->closed);

        throw new FailureException("Pool closed before an active connection could be obtained");
    }

    /**
     * @param Link $connection
     *
     * @throws \Error If the connection is not part of this pool.
     */
    protected function push(Link $connection)
    {
        \assert(isset($this->connections[$connection]), 'Connection is not part of this pool');

        if ($connection->isAlive()) {
            $this->idle->push($connection);
        } else {
            $this->connections->detach($connection);
        }

        if ($this->deferred instanceof Deferred) {
            $this->deferred->resolve($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $sql): Promise
    {
        return call(function () use ($sql) {
            $connection = yield from $this->pop();
            \assert($connection instanceof Link);

            try {
                $result = yield $connection->query($sql);
            } catch (\Throwable $exception) {
                $this->push($connection);
                throw $exception;
            }

            if ($result instanceof ResultSet) {
                $result = $this->createResultSet($result, function () use ($connection) {
                    $this->push($connection);
                });
            } else {
                $this->push($connection);
            }

            return $result;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $sql, array $params = []): Promise
    {
        return call(function () use ($sql, $params) {
            $connection = yield from $this->pop();
            \assert($connection instanceof Link);

            try {
                $result = yield $connection->execute($sql, $params);
            } catch (\Throwable $exception) {
                $this->push($connection);
                throw $exception;
            }

            if ($result instanceof ResultSet) {
                $result = $this->createResultSet($result, function () use ($connection) {
                    $this->push($connection);
                });
            } else {
                $this->push($connection);
            }

            return $result;
        });
    }

    /**
     * {@inheritdoc}
     *
     * Prepared statements returned by this method will stay alive as long as the pool remains open.
     */
    public function prepare(string $sql): Promise
    {
        return call(function () use ($sql) {
            $statement = yield from $this->prepareStatement($sql);
            return $this->createPooledStatement($this, $statement, $this->prepare);
        });
    }

    private function prepareStatement(string $sql): \Generator
    {
        $connection = yield from $this->pop();
        \assert($connection instanceof Link);

        try {
            $statement = yield $connection->prepare($sql);
            \assert($statement instanceof Statement);
        } catch (\Throwable $exception) {
            $this->push($connection);
            throw $exception;
        }

        return $this->createStatement($statement, function () use ($connection) {
            $this->push($connection);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function transaction(int $isolation = Transaction::ISOLATION_COMMITTED): Promise
    {
        return call(function () use ($isolation) {
            $connection = yield from $this->pop();
            \assert($connection instanceof Link);

            try {
                $transaction = yield $connection->transaction($isolation);
                \assert($transaction instanceof Transaction);
            } catch (\Throwable $exception) {
                $this->push($connection);
                throw $exception;
            }

            return $this->createTransaction($transaction, function () use ($connection) {
                $this->push($connection);
            });
        });
    }
}
