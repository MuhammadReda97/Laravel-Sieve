<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class JsonLengthCondition extends BaseCondition
{
    /**
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @param bool $isOr
     */
    public function __construct(public readonly string $field, public readonly string $operator, public readonly mixed $value, public readonly bool $isOr = false)
    {
    }
}