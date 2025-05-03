<?php

namespace RedaLabs\LaravelFilters\Exceptions\Conditions;

use Exception;

class MixedGroupConditionException extends Exception
{
    public function __construct(string $message = "Group Condition Should Be Aggregation's/ non-aggregation's, can not be mixed.")
    {
        parent::__construct($message);
    }
}