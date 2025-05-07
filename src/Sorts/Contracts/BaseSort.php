<?php

namespace RedaLabs\LaravelFilters\Sorts\Contracts;

use Illuminate\Contracts\Database\Query\Builder;

interface BaseSort
{
    /**
     * Apply the sort to the given query builder.
     *
     * @param Builder $builder
     * @return void
     */
    public function apply(Builder $builder): void;
}
