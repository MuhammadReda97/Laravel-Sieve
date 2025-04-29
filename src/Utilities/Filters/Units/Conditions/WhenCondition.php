<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Utilities\Enums\Conditions\LogicalOperatorEnum;

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