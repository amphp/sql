<?php

namespace Amp\Sql;

use Amp\Cancellation;

interface SqlConnector
{
    /**
     * Returns a new database connection based on the given configuration.
     * Implementations may provide further parameters, such as a Cancellation.
     */
    public function connect(SqlConfig $config, ?Cancellation $cancellation = null): Link;
}
