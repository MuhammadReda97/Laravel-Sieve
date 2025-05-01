<?php

namespace RedaLabs\LaravelFilters\Sorts\Contracts;

use Illuminate\Contracts\Database\Query\Builder;

interface BaseSort
{
    public function apply(Builder $builder): void;
}
