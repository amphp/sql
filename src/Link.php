<?php

namespace Amp\Sql;

/**
 * @template TResult of Result
 * @template TStatement of Statement
 * @template TTransaction of Transaction
 *
 * @extends Executor<TResult, TStatement>
 */
interface Link extends Executor
{
    /**
     * Starts a transaction on a single connection.
     *
     * @param TransactionIsolation $isolation Transaction isolation level.'
     *
     * @return TTransaction
     */
    public function beginTransaction(
        TransactionIsolation $isolation = TransactionIsolationLevel::Committed,
    ): Transaction;
}
