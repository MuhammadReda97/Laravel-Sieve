<?php

namespace RedaLabs\LaravelFilters\Utilities\Sorts\Units;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Utilities\Sorts\Abstractions\Sort;

class RawSort extends Sort
{
    public function __construct(public readonly string $expression,
                                public readonly array  $bindings = [])
    {
    }

    public function apply(Builder $builder): void
    {
        $builder->orderByRaw($this->expression, $this->bindings);
    }
}
