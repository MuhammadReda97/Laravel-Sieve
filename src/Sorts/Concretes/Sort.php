<?php

namespace RedaLabs\LaravelFilters\Sorts\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Enums\Sorts\SortDirectionEnum;
use RedaLabs\LaravelFilters\Exceptions\Sorts\InvalidSortDirectionException;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;

class Sort implements BaseSort
{
    private string $field;
    private string $direction;

    /**
     * @param string $field
     * @param string $direction
     */
    public function __construct(string $field, string $direction)
    {
        $this->field = $field;
        $this->direction = strtoupper(trim($direction));
        $this->validateDirection($this->direction);
    }

    /**
     * @param string $direction
     * @return void
     */
    private function validateDirection(string $direction): void
    {
        if (!in_array($direction, SortDirectionEnum::values())) {
            throw new InvalidSortDirectionException($direction);
        }
    }

    /**
     * Apply the sort to the given query builder.
     * @param Builder $builder
     * @return void
     */
    public function apply(Builder $builder): void
    {
        $builder->orderBy($this->getField(), $this->getDirection());
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }
}
