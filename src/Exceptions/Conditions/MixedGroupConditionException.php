<?php

namespace ArchiTools\LaravelSieve\Exceptions\Conditions;

use RuntimeException;

class MixedGroupConditionException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct("Group Condition Should Be Aggregation's/ non-aggregation's, can not be mixed.");
    }
}