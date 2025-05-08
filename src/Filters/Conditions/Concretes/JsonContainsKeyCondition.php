<?php

namespace ArchiTools\LaravelSieve\Filters\Conditions\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use ArchiTools\LaravelSieve\Filters\Conditions\Contracts\BaseCondition;

class JsonContainsKeyCondition extends BaseCondition
{
    /**
     * @param string $field
     * @param string $boolean
     * @param bool $not
     */
    public function __construct(public readonly string $field, string $boolean = 'and', public readonly bool $not = false)
    {
        parent::__construct($boolean);
    }

    public function apply(Builder $builder): void
    {
        $builder->whereJsonContainsKey($this->field, $this->boolean, $this->not);
    }
}