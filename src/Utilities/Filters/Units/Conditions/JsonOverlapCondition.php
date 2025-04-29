<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;

class JsonOverlapCondition extends BaseCondition
{
    /**
     * @param string $field
     * @param mixed $value
     * @param string $boolean
     */
    public function __construct(public readonly string $field, public readonly mixed $value, string $boolean = 'and',public readonly bool $not = false)
    {
        parent::__construct($boolean);
    }

    public function apply(Builder $builder): void
    {
        $builder->whereJsonOverlaps($this->field, $this->value, $this->boolean, $this->not);
    }
}
