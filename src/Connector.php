<?php

namespace Amp\Sql;

interface Connector
{
    /**
     * @param ConnectionConfig $config
     */
    public function connect(ConnectionConfig $config): Link;
}
