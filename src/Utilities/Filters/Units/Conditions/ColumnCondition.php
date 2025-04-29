<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;

class ColumnCondition extends Condition
{
    public function apply(Builder $builder): void
    {
        $builder->whereColumn($this->field, $this->operator, $this->value, $this->boolean);
    }
}