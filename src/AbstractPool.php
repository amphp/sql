<?php

namespace Amp\Sql;

use function Amp\call;
use Amp\CallableMaker;
use function Amp\coroutine;
use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;

abstract class AbstractPool implements Pool
{
    use CallableMaker;

    /** @var Connector */
    private $connector;

    /** @var ConnectionConfig */
    private $config;

    /** @var int */
    private $maxConnections;

    /** @var \SplQueue */
    private $idle;

    /** @var \SplObjectStorage */
    private $connections;

    /** @var bool */
    private $closed = false;

    /** @var string */
    private $timeoutWatcher;

    /** @var int */
    private $idleTimeout = Pool::DEFAULT_IDLE_TIMEOUT;

    /** @var callable */
    private $prepare;

    /** @var int */
    private $lastUsedAt;

    /** @var Promise|null */
    protected $promise;

    /** @var Deferred|null */
    protected $deferred;

    /** @var int Number of pending connections. */
    protected $pending = 0;

    /**
     * @param ConnectionConfig $config
     * @param int $maxConnections
     * @param Connector $connector
     *
     * @throws \Error If $maxConnections is less than 1.
     */
    public function __construct(
        ConnectionConfig $config,
        int $maxConnections = Pool::DEFAULT_MAX_CONNECTIONS,
        Connector $connector = null
    ) {
        $this->connector = $connector ?? $this->defaultConnector();
        $this->config = $config;
        $this->maxConnections = $maxConnections;

        if ($this->maxConnections < 1) {
            throw new \Error("Pool must contain at least one connection");
        }

        $this->connections = $connections = new \SplObjectStorage;
        $this->idle = $idle = new \SplQueue;
        $this->prepare = coroutine($this->callableFromInstanceMethod("doPrepare"));

        $idleTimeout = &$this->idleTimeout;

        $this->timeoutWatcher = Loop::repeat(1000, static function () use (&$idleTimeout, $connections, $idle) {
            $now = \time();
            while (!$idle->isEmpty()) {
                /** @var Connection $connection */
                $connection = $idle->bottom();

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

        $this->lastUsedAt = \time();
    }

    public function __destruct()
    {
        Loop::cancel($this->timeoutWatcher);
    }

    public function query(string $sql): Promise
    {
        return call(function () use ($sql) {
            /** @var Connection $connection */
            $connection = yield from $this->pop();

            try {
                $result = yield $connection->query($sql);
            } catch (\Throwable $exception) {
                $this->push($connection);
                throw $exception;
            }

            if ($result instanceof Operation) {
                $result->onDestruct(function () use ($connection) {
                    $this->push($connection);
                });
            } else {
                $this->push($connection);
            }

            $this->lastUsedAt = \time();

            return $result;
        });
    }

    public function prepare(string $sql): Promise
    {
        return call(function () use ($sql) {
            $statement = yield from $this->doPrepare($sql);

            $this->lastUsedAt = \time();

            return $this->newPooledStatement($this, $statement, $this->prepare);
        });
    }

    public function execute(string $sql, array $params = []): Promise
    {
        return call(function () use ($sql, $params) {
            /** @var Connection $connection */
            $connection = yield from $this->pop();

            try {
                $result = yield $connection->execute($sql, $params);
            } catch (\Throwable $exception) {
                $this->push($connection);
                throw $exception;
            }

            if ($result instanceof Operation) {
                $result->onDestruct(function () use ($connection) {
                    $this->push($connection);
                });
            } else {
                $this->push($connection);
            }

            $this->lastUsedAt = \time();

            return $result;
        });
    }

    public function isAlive(): bool
    {
        return !$this->closed;
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function lastUsedAt(): int
    {
        return $this->lastUsedAt;
    }

    public function transaction(int $isolation = Transaction::ISOLATION_COMMITTED): Promise
    {
        return call(function () use ($isolation) {
            /** @var Connection $connection */
            $connection = yield from $this->pop();

            try {
                /** @var Transaction $transaction */
                $transaction = yield $connection->transaction($isolation);
            } catch (\Throwable $exception) {
                $this->push($connection);
                throw $exception;
            }

            $transaction->onDestruct(function () use ($connection) {
                $this->push($connection);
            });

            $this->lastUsedAt = \time();

            return $transaction;
        });
    }

    /**
     * Extracts an idle connection from the pool. The connection is completely removed from the pool and cannot be
     * put back into the pool. Useful for operations where connection state must be changed.
     *
     * @return Promise<Connection>
     */
    public function extractConnection(): Promise
    {
        return call(function () {
            $connection = yield from $this->pop();
            $this->connections->detach($connection);

            $this->lastUsedAt = \time();

            return $connection;
        });
    }

    public function getConnectionCount(): int
    {
        return $this->connections->count();
    }

    public function getIdleConnectionCount(): int
    {
        return $this->idle->count();
    }

    public function getMaxConnections(): int
    {
        return $this->maxConnections;
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

    private function doPrepare(string $sql): \Generator
    {
        /** @var Connection $connection */
        $connection = yield from $this->pop();

        try {
            /** @var Statement $statement */
            $statement = yield $connection->prepare($sql);
        } catch (\Throwable $exception) {
            $this->push($connection);
            throw $exception;
        }

        \assert(
            $statement instanceof Operation,
            Statement::class . " instances returned from connections must implement " . Operation::class
        );

        $statement->onDestruct(function () use ($connection) {
            $this->push($connection);
        });

        $this->lastUsedAt = \time();

        return $statement;
    }

    abstract protected function defaultConnector(): Connector;

    abstract protected function newPooledStatement(Pool $pool, Statement $statement, callable $prepare): Statement;

    abstract protected function pop(): \Generator;

    abstract protected function push(Connection $connection);
}
