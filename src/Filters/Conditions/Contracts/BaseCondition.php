<?php

namespace RedaLabs\LaravelFilters\Filters\Conditions\Contracts;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Enums\Conditions\LogicalOperatorEnum;
use RedaLabs\LaravelFilters\Exceptions\Conditions\InvalidLogicalOperatorException;

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
