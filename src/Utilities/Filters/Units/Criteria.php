<?php

namespace SortifyLoom\Utilities\Filters\Units;

use Illuminate\Contracts\Database\Query\Builder;
use SortifyLoom\Utilities\Enums\Conditions\GroupConditionTypeEnum;
use SortifyLoom\Utilities\Filters\Units\Conditions\AggregationCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\BaseCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\BetweenCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\Condition;
use SortifyLoom\Utilities\Filters\Units\Conditions\GroupConditions;
use SortifyLoom\Utilities\Filters\Units\Conditions\InCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\JsonContainCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\JsonLengthCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\NotInCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\NotNullCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\NullCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\RawCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\WhenCondition;
use SortifyLoom\Utilities\Filters\Units\Joins\ClosureJoin;
use SortifyLoom\Utilities\Filters\Units\Joins\Join;
use SortifyLoom\Utilities\Filters\Units\Joins\NormalJoin;
use SortifyLoom\Utilities\Sorts\Abstractions\Sort;
use SortifyLoom\Utilities\Sorts\Units\BasicSort;
use SortifyLoom\Utilities\Sorts\Units\RawSort;

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
                GroupConditions::class => $this->applyGroupConditions($builder, $condition),
                WhenCondition::class => $this->applyWhenCondition($builder, $condition),
                NullCondition::class => $this->applyNullCondition($builder, $condition),
                NotNullCondition::class => $this->applyNotNullCondition($builder, $condition),
                Condition::class => $this->applyCondition($builder, $condition),
                AggregationCondition::class => $this->applyAggregationCondition($builder, $condition),
                InCondition::class => $this->applyInCondition($builder, $condition),
                NotInCondition::class => $this->applyNotInCondition($builder, $condition),
                BetweenCondition::class => $this->applyBetweenCondition($builder, $condition),
                JsonContainCondition::class => $this->applyJsonContainCondition($builder, $condition),
                JsonLengthCondition::class => $this->applyJsonLengthCondition($builder, $condition),
                RawCondition::class => $this->applyRawCondition($builder, $condition),
            };
        }

        return $this;
    }

    private function applyGroupConditions(Builder $builder, GroupConditions $condition): void
    {
        $method = $condition->type == GroupConditionTypeEnum::AGGREGATION ? 'having' : 'where';
        $builder->$method(function ($query) use ($condition) {
            $this->applyConditions($condition->conditions, $query);
        });
    }

    private function applyWhenCondition(Builder $builder, WhenCondition $condition): void
    {
        $builder->when($condition->verification, function ($query) use ($condition) {
            $this->applyConditions([$condition->condition], $query);
        });
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
        if ($condition->isOr) {
            $builder->orHaving($condition->field, $condition->operator, $condition->value);
        } else {
            $builder->having($condition->field, $condition->operator, $condition->value);
        }
    }

    private function applyInCondition(Builder $builder, InCondition $condition): void
    {
        $builder->whereIn($condition->field, $condition->values);
    }

    private function applyNotInCondition(Builder $builder, NotInCondition $condition): void
    {
        $builder->whereNotIn($condition->field, $condition->values);
    }

    private function applyBetweenCondition(Builder $builder, BetweenCondition $condition): void
    {
        $builder->whereBetween($condition->field, $condition->values);
    }

    private function applyJsonContainCondition(Builder $builder, JsonContainCondition $condition): void
    {
        $builder->whereRaw("JSON_CONTAINS($condition->field,'$condition->value')");
    }

    private function applyJsonLengthCondition(Builder $builder, JsonLengthCondition $condition): void
    {
        if ($condition->isOr) {
            $builder->orWhereRaw("JSON_LENGTH($condition->field) $condition->operator $condition->value");
        } else {
            $builder->whereRaw("JSON_LENGTH($condition->field) $condition->operator $condition->value");
        }
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

    private function applyRawCondition(Builder $builder, RawCondition $condition): void
    {
        $builder->whereRaw($condition->expression, $condition->bindings);
    }
}
