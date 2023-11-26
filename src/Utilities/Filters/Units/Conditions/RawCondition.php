<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class RawCondition extends BaseCondition
{
    /**
     * @param string $expression
     * @param array $bindings
     */
    public function __construct(public readonly string $expression, public readonly array $bindings = [])
    {
    }
}