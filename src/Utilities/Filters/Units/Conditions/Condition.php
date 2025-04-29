<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Utilities\Enums\Operators\OperatorEnum;
use RedaLabs\LaravelFilters\Utilities\Exceptions\Operators\InvalidOperatorException;

class Condition extends BaseCondition
{
    /**
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @throws InvalidOperatorException
     */
    public function __construct(public readonly string $field, public readonly string $operator, public readonly mixed $value, string $boolean = 'and')
    {
        if (!OperatorEnum::isValid($operator)) {
            throw new InvalidOperatorException($operator);
        }
        parent::__construct($boolean);
    }

    public function apply(Builder $builder): void
    {
        $builder->where($this->field, $this->operator, $this->value, $this->boolean);
    }
}
