<?php

namespace Amp\Sql;

interface Statement extends TransientResource
{
    /**
     * @param mixed[] $params
     */
    public function execute(array $params = []): Result;

    /**
     * @return string The SQL string used to prepare the statement.
     */
    public function getQuery(): string;
}
