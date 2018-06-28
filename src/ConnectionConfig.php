<?php

namespace Amp\Sql;

interface ConnectionConfig {
    public function connectionString(): string;
}
