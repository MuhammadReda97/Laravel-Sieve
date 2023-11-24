<?php

namespace SortifyLoom\Utilities\Exceptions\Conditions;

use Exception;

class InvalidGroupConditionException extends Exception
{
    public function __construct(string $message = "Group Condition Should Be Aggregation's/ non-aggregation's, can not be mixed.")
    {
        parent::__construct($message);
    }
}