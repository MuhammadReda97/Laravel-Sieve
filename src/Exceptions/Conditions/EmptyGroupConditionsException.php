<?php

namespace ArchiTools\LaravelSieve\Exceptions\Conditions;

use RuntimeException;

class EmptyGroupConditionsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The group conditions cannot be empty.');
    }
}