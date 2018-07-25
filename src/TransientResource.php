<?php declare(strict_types=1);

namespace Amp\Sql;

interface TransientResource
{
    /**
     * Indicates if the resource is still valid.
     *
     * @return bool
     */
    public function isAlive(): bool;

    /**
     * Get the timestamp of the last usage of this resource.
     *
     * @return int
     */
    public function lastUsedAt(): int;
}
