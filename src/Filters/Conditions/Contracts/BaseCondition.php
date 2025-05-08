<?php

namespace ArchiTools\LaravelSieve\Filters\Conditions\Contracts;

use Illuminate\Contracts\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Conditions\LogicalOperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Conditions\InvalidLogicalOperatorException;

abstract class BaseCondition
{
    public function __construct(public readonly string $boolean)
    {
        $this->validateBoolean($boolean);
    }

    private function validateBoolean(string $boolean): void
    {
        if (!in_array($boolean, LogicalOperatorEnum::values())) {
            throw new InvalidLogicalOperatorException($boolean);
        }
    }

    public abstract function apply(Builder $builder): void;
}
