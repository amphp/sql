<?php declare(strict_types=1);

namespace Amp\Sql;

/**
 * @template TResult of Result
 * @template TStatement of Statement
 * @template TTransaction of Transaction
 * @template TConfig of SqlConfig
 *
 * @extends Link<TResult, TStatement, TTransaction>
 */
interface Connection extends Link
{
    /**
     * @return TConfig The configuration used to create this connection.
     */
    public function getConfig(): SqlConfig;
}
