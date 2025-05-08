<?php

namespace ArchiTools\LaravelSieve\Filters\Conditions\Concretes;

use Illuminate\Contracts\Database\Query\Builder;

class DateCondition extends Condition
{
    public function apply(Builder $builder): void
    {
        $builder->whereDate($this->field, $this->operator, $this->value, $this->boolean);
    }
}