<?php

namespace ArchiTools\LaravelSieve\Sorts\Contracts;

use Illuminate\Contracts\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Sorts\SortDirectionEnum;
use ArchiTools\LaravelSieve\Exceptions\Sorts\InvalidSortDirectionException;

/**
 * Abstract base class for sort operations.
 * Provides common functionality for handling sort direction.
 */
abstract class BaseSort
{
    protected string $direction;

    /**
     * Creates a new sort instance.
     * 
     * @param string $direction The sort direction ('ASC' or 'DESC')
     * @throws InvalidSortDirectionException If the direction is not valid
     */
    public function __construct(string $direction)
    {
        $this->setDirection($direction);
    }

    /**
     * Sets the sort direction and validates it.
     * 
     * @param string $direction The direction to set
     * @throws InvalidSortDirectionException If the direction is not valid
     */
    protected function setDirection(string $direction): void
    {
        $direction = strtoupper(trim($direction));
        if (!in_array($direction, SortDirectionEnum::values())) {
            throw new InvalidSortDirectionException($direction);
        }
        $this->direction = $direction;
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

    /**
     * Applies the sort to the given query builder.
     * 
     * @param Builder $builder The query builder to modify
     */
    abstract public function apply(Builder $builder): void;
}
