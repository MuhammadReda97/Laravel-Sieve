<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class BetweenCondition extends BaseCondition
{
    /**
     * @param string $field
     * @param array $values
     */
    public function __construct(public readonly string $field, public readonly array $values)
    {
    }

}