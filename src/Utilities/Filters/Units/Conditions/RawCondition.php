<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class RawCondition extends BaseCondition
{
    public function __construct(public readonly string $expression, public readonly array $bindings = [])
    {
    }
}