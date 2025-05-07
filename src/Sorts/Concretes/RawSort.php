<?php

namespace RedaLabs\LaravelFilters\Sorts\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;

readonly class RawSort implements BaseSort
{
    /**
     * @param string $expression
     * @param array $bindings
     */
    public function __construct(public string $expression,
                                public array  $bindings = [])
    {
    }

    /**
     * Apply the sort to the given query builder.
     * @param Builder $builder
     * @return void
     */
    public function apply(Builder $builder): void
    {
        $builder->orderByRaw($this->expression, $this->bindings);
    }
}
