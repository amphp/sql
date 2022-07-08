<?php

namespace Amp\Sql;

/**
 * @template TResult extends Result
 */
interface Statement extends TransientResource
{
    /**
     * @return TResult
     */
    public function execute(array $params = []): Result;

    /**
     * @return string The SQL string used to prepare the statement.
     */
    public function getQuery(): string;
}
