<?php

namespace RedaLabs\LaravelFilters\Exceptions\Conditions;

use Exception;

class MixedGroupConditionException extends Exception
{
    public function __construct()
    {
        parent::__construct("Group Condition Should Be Aggregation's/ non-aggregation's, can not be mixed.");
    }
}