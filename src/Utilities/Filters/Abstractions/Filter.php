<?php

namespace SortifyLoom\Utilities\Filters\Abstractions;

use SortifyLoom\Utilities\Filters\Units\Criteria;

abstract class Filter
{
    protected string $field;

    public abstract static function filter(Criteria $criteria, mixed $value): void;

    public function setField(string $field): void
    {
        $this->field = $field;
    }
}
