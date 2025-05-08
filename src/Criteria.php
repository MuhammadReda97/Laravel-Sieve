<?php

namespace ArchiTools\LaravelSieve;

use Illuminate\Contracts\Database\Query\Builder;
use ArchiTools\LaravelSieve\Filters\Conditions\Contracts\BaseCondition;
use ArchiTools\LaravelSieve\Filters\Joins\Contracts\BaseJoin;
use ArchiTools\LaravelSieve\Sorts\Concretes\Sort;
use ArchiTools\LaravelSieve\Sorts\Contracts\BaseSort;

/**
 * Main orchestrator class for managing and applying query modifications.
 * This class handles the collection and application of joins, conditions, and sorts to a query builder.
 */
class Criteria
{
    /**
     * @var BaseCondition[] Array of conditions to be applied to the query
     */
    private array $conditions = [];

    /**
     * @var BaseJoin[] Array of joins to be applied to the query
     */
    private array $joins = [];

    /**
     * @var Sort[] Array of sorts to be applied to the query
     */
    private array $sorts = [];

    /**
     * @var string Key used for sorting joins by their order
     */
    private string $joinOrderKey = 'order';

    /**
     * Adds a join to the criteria with an optional sort order.
     *
     * @param BaseJoin $join The join to be added
     * @param int $sort The order in which the join should be applied (default: 100)
     * @return $this
     */
    public function appendJoin(BaseJoin $join, int $sort = 100): self
    {
        $this->joins[$join->name] = [
            $this->joinOrderKey => $sort,
            $join->name => $join
        ];
        return $this;
    }

    /**
     * Adds a sort to the criteria.
     *
     * @param BaseSort $sort The sort to be added
     * @return $this
     */
    public function appendSort(BaseSort $sort): self
    {
        if ($sort instanceof Sort) {
            $this->sorts[$sort->getField()] = $sort;
            return $this;
        }
        $this->sorts[] = $sort;
        return $this;
    }

    /**
     * Removes a join from the criteria if it exists.
     *
     * @param string $joinName The name of the join to remove
     * @return $this
     */
    public function removeJoinIfExists(string $joinName): self
    {
        if (isset($this->joins[$joinName])) {
            unset($this->joins[$joinName]);
        }

        return $this;
    }

    /**
     * Checks if a join exists in the criteria.
     *
     * @param string $joinName The name of the join to check
     * @return bool True if the join exists, false otherwise
     */
    public function joinExists(string $joinName): bool
    {
        return isset($this->joins[$joinName]);
    }

    /**
     * Adds a condition to the criteria.
     *
     * @param BaseCondition $condition The condition to be added
     * @return $this
     */
    public function appendCondition(BaseCondition $condition): self
    {
        $this->conditions[] = $condition;
        return $this;
    }

    /**
     * Applies all modifications (joins, conditions, and sorts) to the query builder.
     *
     * @param Builder $builder The query builder to modify
     * @return Builder The modified query builder
     */
    public function applyOnBuilder(Builder $builder): Builder
    {
        $this->applyJoins($builder)
            ->applyConditions($builder, $this->conditions)
            ->applySorts($builder);

        return $builder;
    }

    /**
     * Applies all sorts to the query builder.
     *
     * @param Builder $builder The query builder to modify
     * @return $this
     */
    public function applySorts(Builder $builder): self
    {
        foreach ($this->sorts as $sort) {
            $sort->apply($builder);
        }
        return $this;
    }

    /**
     * Applies all conditions to the query builder.
     *
     * @param Builder $builder The query builder to modify
     * @param BaseCondition[] $conditions Array of conditions to apply
     * @return $this
     */
    public function applyConditions(Builder $builder, array $conditions): self
    {
        foreach ($conditions as $condition) {
            $condition->apply($builder);
        }

        return $this;
    }

    /**
     * Applies all joins to the query builder in their specified order.
     *
     * @param Builder $builder The query builder to modify
     * @return $this
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
}
