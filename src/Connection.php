<?php declare(strict_types=1);

namespace Amp\Sql;

/**
 * @template TConfig of SqlConfig
 * @template TResult of Result
 * @template TStatement of Statement<TResult>
 * @template TTransaction of Transaction
 *
 * @extends Link<TResult, TStatement, TTransaction>
 */
interface Connection extends Link
{
    /**
     * @return TConfig The configuration used to create this connection.
     */
    public function getConfig(): SqlConfig;

    /**
     * @return TransactionIsolation Current transaction isolation used when beginning transactions on this connection.
     */
    public function getTransactionIsolation(): TransactionIsolation;

    /**
     * Sets the transaction isolation level for transactions began on this link.
     *
     * @see Link::beginTransaction()
     */
    public function setTransactionIsolation(TransactionIsolation $isolation): void;
}
