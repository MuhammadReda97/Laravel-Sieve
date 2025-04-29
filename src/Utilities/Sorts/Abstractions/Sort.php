<?php

namespace RedaLabs\LaravelFilters\Utilities\Sorts\Abstractions;

use Illuminate\Contracts\Database\Query\Builder;

abstract class Sort
{
    abstract public function apply(Builder $builder): void;
}
