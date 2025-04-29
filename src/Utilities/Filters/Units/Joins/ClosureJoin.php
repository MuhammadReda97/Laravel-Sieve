<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Joins;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;

class ClosureJoin extends Join
{
    public function __construct(string $table, public readonly Closure $closure, string $type = 'inner', ?string $name = null)
    {
        parent::__construct($table, $type, $name);
    }

    public function apply(Builder $builder): void
    {
        $builder->join($this->table, $this->closure, type: $this->type);
    }
}
