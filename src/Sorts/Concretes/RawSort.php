<?php

namespace RedaLabs\LaravelFilters\Sorts\Concretes;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;

/**
 * Represents a raw SQL sort operation on a database query.
 * This class allows for custom SQL expressions to be used in sorting operations.
 */
class RawSort extends BaseSort
{
    private string $expression;
    private array $bindings;

    /**
     * Creates a new RawSort instance.
     *
     * @param string $expression The raw SQL expression to sort by
     * @param string $direction The sort direction ('ASC' or 'DESC')
     * @param array $bindings The bindings for the raw SQL expression
     */
    public function __construct(string $expression, string $direction, array $bindings = [])
    {
        parent::__construct($direction);
        $this->expression = $expression;
        $this->bindings = $bindings;
    }

    /**
     * Gets the raw SQL expression being used for sorting.
     *
     * @return string The raw SQL expression
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * Gets the bindings for the raw SQL expression.
     *
     * @return array The bindings array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Applies the raw sort operation to the given query builder.
     *
     * @param Builder $builder The query builder to apply the sort to
     */
    public function apply(Builder $builder): void
    {
        $builder->orderByRaw($this->getExpression() . ' ' . $this->getDirection(), $this->getBindings());
    }
}
