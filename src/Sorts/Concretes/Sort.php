<?php

namespace RedaLabs\LaravelFilters\Sorts\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;

class Sort implements BaseSort
{
    /**
     * @param string $field
     * @param string $direction
     */
    public function __construct(public readonly string $field, public readonly string $direction)
    {
    }

    public function apply(Builder $builder): void
    {
        $builder->orderBy($this->field, $this->direction);
    }
}
