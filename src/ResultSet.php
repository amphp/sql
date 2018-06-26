<?php

namespace Amp\Sql;

use Amp\Iterator;
use Amp\Promise;

interface ResultSet extends Iterator {
    const FETCH_ARRAY = 0;
    const FETCH_ASSOC = 1;
    const FETCH_OBJECT = 2;

    /**
     * {@inheritdoc}
     *
     * @param int $type Next row fetch type. Use the FETCH_* constants provided by this interface.
     */
    public function advance(int $type = self::FETCH_ASSOC): Promise;
}
