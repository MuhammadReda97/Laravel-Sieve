<?php

namespace RedaLabs\LaravelFilters\Sorts\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;

/**
 * Represents a sort operation on a database query.
 * This class handles the sorting of query results by a specific field in a specified direction.
 */
class Sort extends BaseSort
{
    private string $field;

    /**
     * Creates a new Sort instance.
     *
     * @param string $field The database column to sort by
     * @param string $direction The sort direction ('ASC' or 'DESC')
     */
    public function __construct(string $field, string $direction)
    {
        parent::__construct($direction);
        $this->field = $field;
    }

    /**
     * Gets the field (column) being sorted by.
     *
     * @return string The field name
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Applies the sort operation to the given query builder.
     *
     * @param Builder $builder The query builder to apply the sort to
     */
    public function apply(Builder $builder): void
    {
        $builder->orderBy($this->getField(), $this->getDirection());
    }
}
