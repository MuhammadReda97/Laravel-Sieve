<?php

namespace ArchiTools\LaravelSieve\Filters\Contracts;

use ArchiTools\LaravelSieve\Criteria;

interface Filter
{
    public function apply(Criteria $criteria, mixed $value): void;
}
