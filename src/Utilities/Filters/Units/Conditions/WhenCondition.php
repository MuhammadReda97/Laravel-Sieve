<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

class WhenCondition extends BaseCondition
{
    /**
     * @param bool $verification
     * @param string $condition
     */
    public function __construct(public bool $verification, public readonly mixed $condition)
    {
    }
}