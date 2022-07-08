<?php

namespace Amp\Sql;

/**
 * @template TResult extends Result
 * @template TStatement extends Statement
 * @template TTransaction extends Transaction
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
