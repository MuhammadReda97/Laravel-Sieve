<?php

namespace Tests\Unit\Core;

use RedaLabs\LaravelFilters\Criteria;
use RedaLabs\LaravelFilters\Exceptions\Operators\InvalidOperatorException;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\Condition;
use RedaLabs\LaravelFilters\Filters\Contracts\Filter;

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