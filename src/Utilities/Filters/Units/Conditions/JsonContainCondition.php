<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class JsonContainCondition extends BaseCondition
{
    /**
     * @param string $field
     * @param mixed $value
     */
    public function __construct(public readonly string $field, public readonly mixed $value)
    {
    }
}
