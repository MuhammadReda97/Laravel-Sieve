<?php

namespace RedaLabs\LaravelFilters\Filters\Conditions\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Filters\Conditions\Contracts\BaseCondition;

class BetweenCondition extends BaseCondition
{
    /**
     * @param string $field
     * @param array $values
     * @param string $boolean
     * @param bool $not
     */
    public function __construct(public readonly string $field, public readonly array $values, string $boolean = 'and', public readonly bool $not = false)
    {
        if (count($values) !== 2) {
            throw new \InvalidArgumentException('Between condition requires exactly two values.');
        }

        parent::__construct($boolean);
    }

    public function apply(Builder $builder): void
    {
        $builder->whereBetween($this->field, $this->values, $this->boolean, $this->not);
    }
}