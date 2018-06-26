<?php

namespace Amp\Sql;

use Amp\Promise;

interface Connection extends Link {
    /**
     * @return bool False if the connection has been closed.
     */
    public function isAlive(): bool;

    /**
     * @return int Timestamp of the last time this connection was used.
     */
    public function lastUsedAt(): int;

    public function close();

    public function query(string $query): Promise;

    public function transaction(int $isolation = Transaction::COMMITTED): Promise;

    public function prepare(string $query): Promise;

    /**
     * {@inheritdoc}
     */
    public function execute(string $sql, array $params = []): Promise;
}
