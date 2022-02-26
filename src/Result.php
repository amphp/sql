<?php

namespace Amp\Sql;

interface Result extends \Traversable
{
    /**
     * Resolves with a new instance of Result if another result is available after this result. Resolves with null if
     * no further results are available.
     */
    public function getNextResult(): ?Result;

    /**
     * Returns the number of rows affected or returned by the query if applicable or null if the number of rows is
     * unknown or not applicable to the query.
     */
    public function getRowCount(): ?int;

    /**
     * Returns the number of columns returned by the query if applicable or null if the number of columns is
     * unknown or not applicable to the query.
     */
    public function getColumnCount(): ?int;
}
