<?php

namespace Amp\Sql;

use Amp\Promise;
use Amp\Stream;

interface Result extends Stream
{
    /**
     * Promise returned resolves with a map (associative array) of column-names to column-values for each row in the
     * result set. The promise resolves with null when no more rows remain.
     *
     * @return Promise<array<string, mixed>|null>
     */
    public function continue(): Promise;

    /**
     * Resolves with a new instance of Result if another result is available after this result. Resolves with null if
     * no further results are available.
     *
     * @return Promise<Result|null>
     */
    public function getNextResult(): Promise;

    /**
     * Returns the number of rows affected or returned by the query if applicable or null if the number of rows is
     * unknown or not applicable to the query.
     *
     * @return int|null
     */
    public function getRowCount(): ?int;
}
