<?php

namespace Amp\Sql;

enum TransactionIsolation
{
    case Uncommitted;
    case Committed;
    case Repeatable;
    case Serializable;
}
