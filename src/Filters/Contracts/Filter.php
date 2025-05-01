<?php

namespace RedaLabs\LaravelFilters\Filters\Contracts;

use RedaLabs\LaravelFilters\Criteria;

interface Filter
{
    public function apply(Criteria $criteria, mixed $value): void;
}
