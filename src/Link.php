<?php declare(strict_types=1);

namespace Amp\Sql;

/**
 * @template TResult of Result
 * @template TStatement of Statement<TResult>
 * @template TTransaction of Transaction
 *
 * @extends Executor<TResult, TStatement>
 */
interface Link extends Executor
{
    /**
     * Starts a transaction, returning an object where all queries are executed on a single connection.
     *
     * @return TTransaction
     */
    public function beginTransaction(): Transaction;
}
