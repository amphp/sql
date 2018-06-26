<?php

namespace Amp\Sql;

use Amp\Promise;

interface Pool extends Link
{
    const DEFAULT_MAX_CONNECTIONS = 100;
    const DEFAULT_IDLE_TIMEOUT = 60;

    public function getIdleTimeout(): int;

    /**
     * @param int $timeout The maximum number of seconds a connection may be idle before being closed and removed
     *     from the pool.
     *
     * @throws \Error If the timeout is less than 1.
     */
    public function setIdleTimeout(int $timeout);

    public function getMaxConnections(): int;

    public function getConnectionCount(): int;

    public function getIdleConnectionCount(): int;

    /**
     * Extracts an idle connection from the pool. The connection is completely removed from the pool and cannot be
     * put back into the pool. Useful for operations where connection state must be changed.
     *
     * @return \Amp\Promise<\Amp\Sql\Connection>
     */
    public function extractConnection(): Promise;

    /**
     * {@inheritdoc}
     */
    public function transaction(int $isolation = Transaction::COMMITTED): Promise;
}
