<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class BetweenCondition extends BaseCondition
{
    public function __construct(public readonly string $field, public readonly array $values)
    {
    }

}