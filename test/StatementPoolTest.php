<?php

namespace Amp\Sql\Test;

use Amp\Delayed;
use Amp\Loop;
use Amp\PHPUnit\TestCase;
use Amp\Sql\Pool;
use Amp\Sql\Statement;
use Amp\Sql\StatementPool;

class StatementPoolTest extends TestCase
{
    public function testActiveStatementsRemainAfterTimeout()
    {
        Loop::run(function () {
            $pool = $this->createMock(Pool::class);
            $pool->method('isAlive')
                ->willReturn(true);
            $pool->method('getIdleTimeout')
                ->willReturn(60);

            $statement = $this->createMock(Statement::class);
            $statement->method('isAlive')
                ->willReturn(true);
            $statement->method('getQuery')
                ->willReturn('SELECT 1');
            $statement->method('lastUsedAt')
                ->willReturn(\time());
            $statement->expects($this->once())
                ->method('execute');

            /** @var StatementPool $statementPool */
            $statementPool = $this->getMockBuilder(StatementPool::class)
                ->setConstructorArgs([$pool, $statement, $this->createCallback(0)])
                ->getMockForAbstractClass();

            $this->assertTrue($statementPool->isAlive());
            $this->assertSame(\time(), $statementPool->lastUsedAt());

            yield new Delayed(1500); // Give timeout watcher enough time to execute.

            $statementPool->execute();

            $this->assertTrue($statementPool->isAlive());
            $this->assertSame(\time(), $statementPool->lastUsedAt());
        });
    }

    public function testIdleStatementsRemovedAfterTimeout()
    {
        Loop::run(function () {
            $pool = $this->createMock(Pool::class);
            $pool->method('isAlive')
                ->willReturn(true);
            $pool->method('getIdleTimeout')
                ->willReturn(1);

            $statement = $this->createMock(Statement::class);
            $statement->method('isAlive')
                ->willReturn(true);
            $statement->method('getQuery')
                ->willReturn('SELECT 1');
            $statement->method('lastUsedAt')
                ->willReturn(\time());
            $statement->expects($this->once())
                ->method('execute');

            /** @var StatementPool $statementPool */
            $statementPool = $this->getMockBuilder(StatementPool::class)
                ->setConstructorArgs([$pool, $statement, $this->createCallback(1)])
                ->getMockForAbstractClass();

            $this->assertTrue($statementPool->isAlive());
            $this->assertSame(\time(), $statementPool->lastUsedAt());

            $statementPool->execute();

            yield new Delayed(1500); // Give timeout watcher enough time to execute.

            $statementPool->execute();

            $this->assertTrue($statementPool->isAlive());
            $this->assertSame(\time(), $statementPool->lastUsedAt());
        });
    }
}
