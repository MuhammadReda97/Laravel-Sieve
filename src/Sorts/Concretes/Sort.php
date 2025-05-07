<?php

namespace RedaLabs\LaravelFilters\Sorts\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Enums\Sorts\SortDirectionEnum;
use RedaLabs\LaravelFilters\Exceptions\Sorts\InvalidSortDirectionException;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;

/**
 * Represents a sort operation on a database query.
 * This class handles the sorting of query results by a specific field in a specified direction.
 */
class Sort implements BaseSort
{
    private string $field;
    private string $direction;

    /**
     * Creates a new Sort instance.
     *
     * @param string $field The database column to sort by
     * @param string $direction The sort direction ('ASC' or 'DESC')
     * @throws InvalidSortDirectionException If the direction is not valid
     */
    public function __construct(string $field, string $direction)
    {
        $this->field = $field;
        $this->direction = strtoupper(trim($direction));
        $this->validateDirection($this->direction);
    }

    /**
     * Validates that the sort direction is one of the allowed values.
     *
     * @param string $direction The direction to validate
     * @throws InvalidSortDirectionException If the direction is not valid
     */
    private function validateDirection(string $direction): void
    {
        if (!in_array($direction, SortDirectionEnum::values())) {
            throw new InvalidSortDirectionException($direction);
        }
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
     * Gets the sort direction.
     *
     * @return string The sort direction ('ASC' or 'DESC')
     */
    public function getDirection(): string
    {
        return $this->direction;
    }
}
