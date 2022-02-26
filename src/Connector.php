<?php

namespace Amp\Sql;

interface Connector
{
    /**
     * Returns a new database connection based on the given configuration.
     * Implementations may provide further parameters, such as a Cancellation.
     */
    public function connect(ConnectionConfig $config): Link;
}
