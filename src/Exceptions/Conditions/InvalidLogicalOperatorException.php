<?php

namespace RedaLabs\LaravelFilters\Exceptions\Conditions;

use RedaLabs\LaravelFilters\Enums\Conditions\LogicalOperatorEnum;
use RuntimeException;

class InvalidLogicalOperatorException extends RuntimeException
{
    public function __construct(string $logicalOperator)
    {
        parent::__construct("Invalid logical operator: '{$logicalOperator}'. Allowed operators are: " . implode(', ', LogicalOperatorEnum::values()));
    }
}