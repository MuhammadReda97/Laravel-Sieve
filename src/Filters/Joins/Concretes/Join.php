<?php

namespace ArchiTools\LaravelSieve\Filters\Joins\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Joins\JoinTypeEnum;
use ArchiTools\LaravelSieve\Enums\Operators\OperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Operators\InvalidOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Contracts\BaseCondition;
use ArchiTools\LaravelSieve\Filters\Joins\Contracts\BaseJoin;

class Join extends BaseJoin
{
    /**
     * @var BaseCondition[]
     */
    private array $conditions = [];

    /**
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @param string $type
     * @param string|null $name
     * @throws InvalidOperatorException
     */
    public function __construct(string $table, public readonly string $first, public readonly string $operator, public readonly string $second, string $type = JoinTypeEnum::INNER->value, ?string $name = null)
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
