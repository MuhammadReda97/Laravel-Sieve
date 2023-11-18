<?php

namespace SortifyLoom\Utilities\Filters\Units;

use SortifyLoom\Utilities\Filters\Units\Conditions\AggregationCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\BaseCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\Condition;
use SortifyLoom\Utilities\Filters\Units\Conditions\InCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\NotInCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\NotNullCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\NullCondition;
use SortifyLoom\Utilities\Filters\Units\Joins\ClosureJoin;
use SortifyLoom\Utilities\Filters\Units\Joins\Join;
use SortifyLoom\Utilities\Filters\Units\Joins\NormalJoin;
use SortifyLoom\Utilities\Sorts\Abstractions\Sort;
use SortifyLoom\Utilities\Sorts\Units\BasicSort;
use SortifyLoom\Utilities\Sorts\Units\RawSort;
use Illuminate\Contracts\Database\Query\Builder;

class Criteria
{
    /**
     * @var BaseCondition[]
     */
    private array $conditions = [];

    /**
     * @var Join[]
     */
    private array $joins = [];

    /**
     * @var Sort[]
     */
    private array $sorts = [];
    private string $sortKey = 'sort';

    public function appendJoin(Join $join, int $sort = 100): self
    {
        $this->joins[$join->name] = [
            $this->sortKey => $sort,
            $join->name => $join
        ];
        return $this;
    }

    public function appendSort(Sort $sort): self
    {
        $this->sorts[] = $sort;
        return $this;
    }

    public function removeJoinIfExists(string $joinName): self
    {
        if (!isset($this->joins[$joinName]))
            return $this;

        unset($this->joins[$joinName]);
        return $this;
    }

    public function appendCondition(BaseCondition $condition): self
    {
        $this->conditions[] = $condition;
        return $this;
    }


    /**
     * @param Builder $builder
     * @return Builder
     */
    public function applyOnBuilder(Builder $builder): Builder
    {
        $this->applyJoins($builder)
            ->applyConditions($this->conditions, $builder)
            ->applySorts($builder);

        return $builder;
    }

    /**
     * @param Builder $builder
     * @return self
     */
    private function applyJoins(Builder $builder): self
    {
        array_multisort(array_column($this->joins, $this->sortKey), SORT_ASC, $this->joins);
        foreach ($this->joins as $joinName => $join) {
            $join = $join[$joinName];
            if ($join instanceof ClosureJoin) {
                /**
                 * @var ClosureJoin $join
                 */
                $builder->join($join->table, $join->closure, type: $join->type);
                continue;
            }
            /**
             * @var NormalJoin $join
             */
            $builder->join($join->table, function ($joinBuilder) use ($join) {
                $joinBuilder = $joinBuilder->on($join->first, $join->operation, $join->second);
                $this->applyConditions($join->getConditions(), $joinBuilder);
            }, type: $join->type);
        }

        return $this;
    }

    /**
     * @param BaseCondition[] $conditions
     * @param Builder $builder
     * @return self
     */
    private function applyConditions(array $conditions, Builder $builder): self
    {
        foreach ($conditions as $condition) {
            match (get_class($condition)) {
                NullCondition::class => $this->applyNullCondition($builder, $condition),
                NotNullCondition::class => $this->applyNotNullCondition($builder, $condition),
                Condition::class => $this->applyCondition($builder, $condition),
                AggregationCondition::class => $this->applyAggregationCondition($builder, $condition),
                InCondition::class => $this->applyInCondition($builder, $condition),
                NotInCondition::class => $this->applyNotInCondition($builder, $condition)
            };
        }

        return $this;
    }

    private function applyNullCondition(Builder $builder, NullCondition $condition): void
    {
        $builder->whereNull($condition->field);
    }

    private function applyNotNullCondition(Builder $builder, NotNullCondition $condition): void
    {
        $builder->whereNotNull($condition->field);
    }

    private function applyCondition(Builder $builder, Condition $condition): void
    {
        if ($condition->isOr) {
            $builder->orWhere($condition->field, $condition->operator, $condition->value);
        } else {
            $builder->where($condition->field, $condition->operator, $condition->value);
        }
    }

    private function applyAggregationCondition(Builder $builder, AggregationCondition $condition): void
    {
        $builder->having($condition->field, $condition->operator, $condition->value);
    }

    private function applyInCondition(Builder $builder, InCondition $condition): void
    {
        $builder->whereIn($condition->field, $condition->values);
    }

    private function applyNotInCondition(Builder $builder, NotInCondition $condition): void
    {
        $builder->whereNotIn($condition->field, $condition->values);
    }

    public function applySorts(Builder $builder): self
    {
        foreach ($this->sorts as $sort) {
            if ($sort instanceof RawSort) {
                $builder->orderByRaw($sort->expression);
            } else {
                /**@var BasicSort $sort */
                $builder->orderBy($sort->field, $sort->direction);
            }
        }
        return $this;
    }
}
