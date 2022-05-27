<?php

namespace Amp\Sql;

enum TransactionIsolationLevel implements TransactionIsolation
{
    case Uncommitted;
    case Committed;
    case Repeatable;
    case Serializable;

    public function getLabel(): string
    {
        return match ($this) {
            self::Uncommitted => 'Uncommitted',
            self::Committed => 'Committed',
            self::Repeatable => 'Repeatable',
            self::Serializable => 'Serializable',
        };
    }

    public function toSql(): string
    {
        return match ($this) {
            self::Uncommitted => 'READ UNCOMMITTED',
            self::Committed => 'READ COMMITTED',
            self::Repeatable => 'REPEATABLE READ',
            self::Serializable => 'SERIALIZABLE',
        };
    }
}
