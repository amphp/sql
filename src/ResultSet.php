<?php

namespace Amp\Sql;

use Amp\Promise;
use Amp\Stream;

interface ResultSet extends Stream
{
    /**
     * Promise returned resolves with a map (associative array) of column-names to column-values for each row in the
     * result set. The promise resolves with null when no more rows remain.
     *
     * @return Promise<array<string, mixed>|null>
     */
    public function continue(): Promise;

    /**
     * Resolves with a new instance of ResultSet if another result is available after this result.
     *
     * @return Promise<ResultSet|null>
     */
    public function getNextResultSet(): Promise;
}
