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

    public function advance(): Promise
    {
        return $this->result->advance();
    }

    public function getCurrent(int $type = self::FETCH_ASSOC)
    {
        return $this->result->getCurrent($type);
    }
}
