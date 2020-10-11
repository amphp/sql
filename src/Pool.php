<?php

namespace Amp\Sql;

interface Pool extends Link
{
    /**
     * @return Link
     */
    public function extractConnection(): Link;

    /**
     * @return int Total number of active connections in the pool.
     */
    public function getConnectionCount(): int;

    /**
     * @return int Total number of idle connections in the pool.
     */
    public function getIdleConnectionCount(): int;

    /**
     * @return int Maximum number of connections this pool will create.
     */
    public function getConnectionLimit(): int;

    /**
     * @return int Number of seconds a connection may remain idle before it is automatically closed.
     */
    public function getIdleTimeout(): int;
}
