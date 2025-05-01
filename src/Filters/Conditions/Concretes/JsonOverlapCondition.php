<?php

namespace RedaLabs\LaravelFilters\Filters\Conditions\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Filters\Conditions\Contracts\BaseCondition;

class JsonOverlapCondition extends BaseCondition
{
    /**
     * @param string $field
     * @param mixed $value
     * @param string $boolean
     * @param bool $not
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
