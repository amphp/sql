<?php

namespace Amp\Sql;

use Amp\Promise;

interface Connector
{
    /**
     * @param ConnectionConfig $config
     *
     * @return Promise<Link>
     */
    public function connect(ConnectionConfig $config): Promise;
}
