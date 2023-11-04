<?php declare(strict_types=1);

namespace Amp\Sql;

/**
 * @template TResult of Result
 * @template TStatement of Statement
 * @template TTransaction of Transaction
 *
 * @extends Executor<TResult, TStatement, TTransaction>
 */
interface Link extends Executor
{
    /**
     * Sets the transaction isolation level for transactions began on this link.
     *
     * @see Executor::beginTransaction()
     */
    public function setTransactionIsolation(TransactionIsolation $isolation): void;
}
