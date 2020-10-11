<?php

namespace Amp\Sql;

interface Link extends Executor
{
    /**
     * Starts a transaction on a single connection.
     *
     * @param int $isolation Transaction isolation level.
     *
     * @return Transaction
     */
    public function beginTransaction(int $isolation = Transaction::ISOLATION_COMMITTED): Transaction;
}
