<?php

namespace RedaLabs\LaravelFilters\Filters\Joins\Concretes;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Filters\Joins\Contracts\BaseJoin;

class ClosureJoin extends BaseJoin
{
    /**
     * @param string $table
     * @param Closure $closure
     * @param string $type
     * @param string|null $name
     */
    public function __construct(string $table, public readonly Closure $closure, string $type = 'inner', ?string $name = null)
    {
        parent::__construct($table, $type, $name);
    }

    public function apply(Builder $builder): void
    {
        $builder->join($this->table, $this->closure, type: $this->type);
    }
}
