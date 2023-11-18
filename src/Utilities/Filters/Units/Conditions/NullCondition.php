<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class NullCondition extends BaseCondition
{
    public function __construct(public readonly string $field)
    {
    }
}
