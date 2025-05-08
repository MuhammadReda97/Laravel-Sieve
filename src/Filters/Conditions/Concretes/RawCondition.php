<?php

namespace ArchiTools\LaravelSieve\Filters\Conditions\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use ArchiTools\LaravelSieve\Filters\Conditions\Contracts\BaseCondition;

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