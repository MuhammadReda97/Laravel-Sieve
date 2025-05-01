<?php

namespace RedaLabs\LaravelFilters\Sorts\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;

class RawSort implements BaseSort
{
    /**
     * @param string $expression
     * @param array $bindings
     */
    public function __construct(public readonly string $expression,
                                public readonly array  $bindings = [])
    {
    }

    public function apply(Builder $builder): void
    {
        $builder->orderByRaw($this->expression, $this->bindings);
    }
}
