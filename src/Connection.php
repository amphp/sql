<?php

namespace Amp\Sql;

use Amp\CancellationToken;
use Amp\Promise;

interface Connection extends Link {
    /**
     * @param ConnectionConfig       $config
     * @param CancellationToken|null $token
     *
     * @return Promise<Connection>
     */
    public static function connect(ConnectionConfig $config, CancellationToken $token = null): Promise;

    /**
     * @return int Timestamp of the last time this connection was used.
     */
    public function lastUsedAt(): int;
}
