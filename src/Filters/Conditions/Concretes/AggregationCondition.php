<?php

namespace ArchiTools\LaravelSieve\Filters\Conditions\Concretes;

use Illuminate\Contracts\Database\Query\Builder;

class AggregationCondition extends Condition
{
    public function apply(Builder $builder): void
    {
        $builder->having($this->field, $this->operator, $this->value, $this->boolean);
    }
}
