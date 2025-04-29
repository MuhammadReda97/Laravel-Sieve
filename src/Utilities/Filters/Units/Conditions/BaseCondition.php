<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Utilities\Enums\Conditions\LogicalOperatorEnum;

abstract class BaseCondition
{
    public function __construct(public readonly string $boolean)
    {
        $this->validateBoolean($boolean);
    }

    public abstract function apply(Builder $builder): void;

    private function validateBoolean(string $boolean): void
    {
        if (!in_array($boolean, LogicalOperatorEnum::values())) {
            $booleanValues = implode(', ', LogicalOperatorEnum::values());
            throw new \InvalidArgumentException("Boolean must be one of {$booleanValues}.");
        }
    }
}
