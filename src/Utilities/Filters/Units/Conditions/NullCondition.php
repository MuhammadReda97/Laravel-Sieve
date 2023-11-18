<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class NullCondition extends BaseCondition
{
    /**
     * @param string $field
     */
    public function __construct(public readonly string $field)
    {
    }
}
