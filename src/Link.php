<?php

namespace Amp\Sql;

use Amp\Promise;

interface Link extends Executor {
    /**
     * Starts a transaction on a single connection.
     *
     * @return \Amp\Promise
     */
    public function transaction(): Promise;
}
