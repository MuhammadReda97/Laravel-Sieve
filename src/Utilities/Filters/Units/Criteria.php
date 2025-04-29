<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions\BaseCondition;
use RedaLabs\LaravelFilters\Utilities\Filters\Units\Joins\Join;
use RedaLabs\LaravelFilters\Utilities\Sorts\Abstractions\Sort;

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
    private string $sortKey = 'sorts';

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
        if (isset($this->joins[$joinName])) {
            unset($this->joins[$joinName]);
        }

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
            ->applyConditions($builder, $this->conditions)
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
            $join->apply($builder);
        }

        return $this;
    }

    /**
     * @param BaseCondition[] $conditions
     * @param Builder $builder
     * @return self
     */
    private function applyConditions(Builder $builder, array $conditions): self
    {
        foreach ($conditions as $condition) {
            $condition->apply($builder);
        }

        return $this;
    }

    public function applySorts(Builder $builder): self
    {
        foreach ($this->sorts as $sort) {
            $sort->apply($builder);
        }
        return $this;
    }
}
