<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class GroupConditions extends BaseCondition
{
    /**
     * @param array $conditions
     */
    public function __construct(public readonly array $conditions)
    {
    }
}