<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;

class JsonContainsKeyCondition extends BaseCondition
{
    public function __construct(public readonly string $field, string $boolean = 'and',public readonly bool $not = false)
    {
        parent::__construct($boolean);
    }

    public function apply(Builder $builder): void
    {
        $builder->whereJsonContainsKey($this->field, $this->boolean, $this->not);
    }
}