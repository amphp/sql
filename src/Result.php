<?php

namespace Amp\Sql;

use Amp\Pipeline\Pipeline;

interface Result extends Pipeline
{
    /**
     * Promise returned resolves with a map (associative array) of column-names to column-values for each row in the
     * result set. The promise resolves with null when no more rows remain.
     *
     * @return array<string, mixed>|null
     */
    public function continue(): ?array;

    /**
     * Resolves with a new instance of Result if another result is available after this result. Resolves with null if
     * no further results are available.
     *
     * @return Result|null
     */
    public function getNextResult(): ?Result;

    /**
     * Returns the number of rows affected or returned by the query if applicable or null if the number of rows is
     * unknown or not applicable to the query.
     *
     * @return int|null
     */
    public function getRowCount(): ?int;
}
