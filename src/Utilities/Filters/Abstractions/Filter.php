<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Abstractions;

use RedaLabs\LaravelFilters\Utilities\Filters\Units\Criteria;

abstract class Filter
{
    protected string $field;

    public function setField(string $field): void
    {
        // todo check use-case.
        $this->field = $field;
    }

    public abstract function filter(Criteria $criteria, mixed $value): void;
}
