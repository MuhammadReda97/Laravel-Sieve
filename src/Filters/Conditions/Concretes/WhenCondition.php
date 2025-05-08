<?php

namespace ArchiTools\LaravelSieve\Filters\Conditions\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Conditions\LogicalOperatorEnum;
use ArchiTools\LaravelSieve\Filters\Conditions\Contracts\BaseCondition;

class WhenCondition extends BaseCondition
{
    /**
     * @param bool $verification
     * @param BaseCondition $condition
     */
    public function __construct(public bool $verification, public readonly BaseCondition $condition)
    {
        parent::__construct(LogicalOperatorEnum::AND->value);
    }

    public function apply(Builder $builder): void
    {
        $builder->when($this->verification, function ($query) {
            $this->condition->apply($query);
        });
    }
}