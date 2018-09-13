<?php

namespace Amp\Sql;

use Amp\Promise;
use function Amp\call;

abstract class PooledStatement implements Statement
{
    /** @var Statement */
    private $statement;

    /** @var callable|null */
    private $release;

    /** @var int */
    private $refCount = 1;

    /**
     * Creates a ResultSet of the appropriate type using the ResultSet object returned by the Statement object and
     * the given release callable.
     *
     * @param ResultSet $resultSet
     * @param callable  $release
     *
     * @return ResultSet
     */
    abstract protected function createResultSet(ResultSet $resultSet, callable $release): ResultSet;

    public function __construct(Statement $statement, callable $release)
    {
        $this->statement = $statement;

        if (!$this->statement->isAlive()) {
            $release();
        } else {
            $refCount = &$this->refCount;
            $this->release = static function () use (&$refCount, $release) {
                if (--$refCount === 0) {
                    $release();
                }
            };
        }
    }

    public function __destruct()
    {
        if ($this->release) {
            ($this->release)();
        }
    }

    public function execute(array $params = []): Promise
    {
        return call(function () use ($params) {
            $result = yield $this->statement->execute($params);

            if ($result instanceof ResultSet) {
                ++$this->refCount;
                return $this->createResultSet($result, $this->release);
            }

            return $result;
        });
    }

    public function isAlive(): bool
    {
        return $this->statement->isAlive();
    }

    public function getQuery(): string
    {
        return $this->statement->getQuery();
    }

    public function getLastUsedAt(): int
    {
        return $this->statement->getLastUsedAt();
    }
}
