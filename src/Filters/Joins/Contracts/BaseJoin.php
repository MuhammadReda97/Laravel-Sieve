<?php

namespace RedaLabs\LaravelFilters\Filters\Joins\Contracts;

use Illuminate\Contracts\Database\Query\Builder;

abstract class BaseJoin
{
    public readonly string $name;

    /**
     * @param string $table
     * @param string $type
     * @param string|null $name
     */
    public function __construct(public readonly string $table, public readonly string $type, ?string $name = null)
    {
        $this->name = $name ?? $this->table;
    }

    abstract public function apply(Builder $builder): void;
}
