<?php

namespace RedaLabs\LaravelFilters\Filters\Conditions\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Filters\Conditions\Contracts\BaseCondition;

class InCondition extends BaseCondition
{
    /**
     * @param string $field
     * @param array $values
     * @param string $boolean
     * @param bool $not
     */
    public function __construct(public readonly string $field, public readonly array $values, string $boolean = 'and',public readonly bool $not = false)
    {
        parent::__construct($boolean);
    }

    public function apply(Builder $builder): void
    {
        $builder->whereIn($this->field, $this->values, $this->boolean, $this->not);
    }
}
