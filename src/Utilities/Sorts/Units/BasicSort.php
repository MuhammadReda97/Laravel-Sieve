<?php

namespace RedaLabs\LaravelFilters\Utilities\Sorts\Units;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Utilities\Sorts\Abstractions\Sort;

class BasicSort extends Sort
{
    public function __construct(public readonly string $field, public readonly string $direction)
    {
    }

    public function apply(Builder $builder): void
    {
        $builder->orderBy($this->field, $this->direction);
    }
}
