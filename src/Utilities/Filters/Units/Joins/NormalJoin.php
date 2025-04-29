<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Joins;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Utilities\Enums\Operators\OperatorEnum;
use RedaLabs\LaravelFilters\Utilities\Exceptions\Operators\InvalidOperatorException;
use RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions\BaseCondition;

class NormalJoin extends Join
{
    /**
     * @var BaseCondition[]
     */
    private array $conditions = [];

    public function __construct(string $table, public readonly string $first, public readonly string $operator, public readonly string $second, string $type = 'inner', ?string $name = null)
    {
        if (!OperatorEnum::isValid($operator)) {
            throw new InvalidOperatorException($operator);
        }
        parent::__construct($table, $type, $name);
    }

    public function appendCondition(BaseCondition $condition): self
    {
        $this->conditions[] = $condition;
        return $this;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function apply(Builder $builder): void
    {
        $builder->join($this->table, function (Builder $joinBuilder) {
            $joinBuilder->on($this->first, $this->operator, $this->second);
            foreach ($this->conditions as $condition) {
                $condition->apply($joinBuilder);
            }
        }, type: $this->type);
    }
}
