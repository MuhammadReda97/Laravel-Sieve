<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;

class JsonLengthCondition extends Condition
{
    public function apply(Builder $builder): void
    {
        $builder->whereJsonLength($this->field, $this->operator, $this->value, $this->boolean);
    }
}