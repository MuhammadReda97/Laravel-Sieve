<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;

class RawCondition extends BaseCondition
{
    /**
     * @param string $expression
     * @param array $bindings
     * @param string $boolean
     */
    public function __construct(public readonly string $expression, public readonly array $bindings = [], string $boolean = 'and')
    {
        parent::__construct($boolean);
    }

    public function apply(Builder $builder): void
    {
        $builder->whereRaw($this->expression, $this->bindings, $this->boolean);
    }
}