<?php

namespace RedaLabs\LaravelFilters;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Filters\Conditions\Contracts\BaseCondition;
use RedaLabs\LaravelFilters\Filters\Joins\Contracts\BaseJoin;
use RedaLabs\LaravelFilters\Sorts\Concretes\Sort;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;

class Criteria
{
    /**
     * @var BaseCondition[]
     */
    private array $conditions = [];

    /**
     * @var BaseJoin[]
     */
    private array $joins = [];

    /**
     * @var Sort[]
     */
    private array $sorts = [];

    private string $joinOrderKey = 'order';

    public function appendJoin(BaseJoin $join, int $sort = 100): self
    {
        $this->joins[$join->name] = [
            $this->joinOrderKey => $sort,
            $join->name => $join
        ];
        return $this;
    }

    public function appendSort(BaseSort $sort): self
    {
        if ($sort instanceof Sort) {
            $this->sorts[$sort->getField()] = $sort;
            return $this;
        }
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

    public function isJoinExists(string $joinName): bool
    {
        return isset($this->joins[$joinName]);
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
    public function applyJoins(Builder $builder): self
    {
        array_multisort(array_column($this->joins, $this->joinOrderKey), SORT_ASC, $this->joins);
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
    public function applyConditions(Builder $builder, array $conditions): self
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
