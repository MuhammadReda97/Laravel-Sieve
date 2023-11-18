<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class Condition extends BaseCondition
{
    public function __construct(public readonly string $field, public readonly string $operator, public readonly mixed $value, public readonly bool $isOr = false)
    {
    }
}
