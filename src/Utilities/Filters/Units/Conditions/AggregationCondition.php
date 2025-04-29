<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;

class AggregationCondition extends Condition
{
    public function apply(Builder $builder): void
    {
        $builder->having($this->field, $this->operator, $this->value, $this->boolean);
    }
}
