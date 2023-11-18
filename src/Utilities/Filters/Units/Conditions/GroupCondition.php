<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class GroupCondition extends BaseCondition
{
    public function __construct(public readonly array $conditions)
    {
    }
}