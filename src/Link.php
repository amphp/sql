<?php

namespace Amp\Sql;

interface Link extends Executor
{
    /**
     * Starts a transaction on a single connection.
     *
     * @param TransactionIsolation $isolation Transaction isolation level.
     *
     * @return Transaction
     */
    public function beginTransaction(TransactionIsolation $isolation = TransactionIsolation::COMMITTED): Transaction;
}
