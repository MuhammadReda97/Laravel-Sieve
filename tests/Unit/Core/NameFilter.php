<?php

namespace Tests\Unit\Core;

use ArchiTools\LaravelSieve\Criteria;
use ArchiTools\LaravelSieve\Exceptions\Operators\InvalidOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\Condition;
use ArchiTools\LaravelSieve\Filters\Contracts\Filter;

readonly class NameFilter implements Filter
{
    public function __construct(private string $column)
    {
    }

    /**
     * @throws InvalidOperatorException
     */
    public function apply(Criteria $criteria, mixed $value): void
    {
        $criteria->appendCondition(new Condition($this->column, 'like', "%{$value}%"));
    }
}