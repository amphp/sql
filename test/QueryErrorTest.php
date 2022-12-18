<?php

namespace Amp\Sql\Test;

use Amp\Sql\QueryError;
use PHPUnit\Framework\TestCase;

class QueryErrorTest extends TestCase
{
    /**
     * @test
     */
    public function testItPassesQueryAlong()
    {
        $error = new QueryError('error', 'SELECT * FROM foo');

        $this->assertSame('SELECT * FROM foo', $error->getQuery());
        $this->assertStringStartsWith("Amp\Sql\QueryError: error\nCurrent query was SELECT * FROM foo", (string) $error);
    }
}
