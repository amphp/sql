<?php

namespace Amp\Sql;

use Amp\Promise;

class PooledResultSet implements ResultSet
{
    /** @var ResultSet */
    private $result;

    /** @var callable */
    private $release;

    public function __construct(ResultSet $result, callable $release)
    {
        $this->result = $result;
        $this->release = $release;
    }

    public function __destruct()
    {
        ($this->release)();
    }

    public function advance(int $type = self::FETCH_ASSOC): Promise
    {
        return $this->result->advance($type);
    }

    public function getCurrent()
    {
        return $this->result->getCurrent();
    }
}
