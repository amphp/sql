<?php

namespace Amp\Sql;

enum TransactionIsolation
{
    case UNCOMMITTED;
    case COMMITTED;
    case REPEATABLE;
    case SERIALIZABLE;
}
