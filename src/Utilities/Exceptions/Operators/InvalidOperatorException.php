<?php

namespace RedaLabs\LaravelFilters\Utilities\Exceptions\Operators;

use Exception;
use RedaLabs\LaravelFilters\Utilities\Enums\Operators\OperatorEnum;

class InvalidOperatorException extends Exception
{
    public function __construct(string $operator)
    {
        parent::__construct("Invalid operator: '{$operator}'. Allowed operators are: " . implode(', ', OperatorEnum::getValues()));
    }
} 