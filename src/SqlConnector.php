<?php

namespace Amp\Sql;

interface SqlConnector
{
    /**
     * Returns a new database connection based on the given configuration.
     * Implementations may provide further parameters, such as a Cancellation.
     */
    public function connect(SqlConfig $config): Link;
}
