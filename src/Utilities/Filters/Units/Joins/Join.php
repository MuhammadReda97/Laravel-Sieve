<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Joins;

use Illuminate\Contracts\Database\Query\Builder;

abstract class Join
{
    public readonly string $name;

    public function __construct(public readonly string $table, public readonly string $type, ?string $name = null)
    {
        $this->name = $name ?? $this->table;
    }

    abstract public function apply(Builder $builder): void;
}
