<?php

namespace ArchiTools\LaravelSieve;

use ArchiTools\LaravelSieve\Enums\Sorts\SortDirectionEnum;
use ArchiTools\LaravelSieve\Filters\Contracts\Filter;
use ArchiTools\LaravelSieve\Sorts\Concretes\Sort;
use ArchiTools\LaravelSieve\Sorts\Contracts\BaseSort;
use Illuminate\Http\Request;

/**
 * Abstract base class for implementing filtering and sorting functionality.
 * This class provides a framework for applying filters and sorts to queries based on request parameters.
 */
abstract class UtilitiesService
{
    /**
     * @var string Default sort direction to use when none is specified
     */
    protected string $defaultSortDirection;

    /**
     * @var string Key used in request parameters for sorts
     */
    protected string $sortsKey = 'sorts';

    /**
     * @var Criteria Instance of Criteria for managing query modifications
     */
    private Criteria $criteria;

    /**
     * @var array Query parameters from the request
     */
    private array $queryParameters = [];

    /**
     * @var array Cache of available sorts
     */
    private array $availableSorts;

    /**
     * Creates a new UtilitiesService instance.
     *
     * @param Criteria $criteria The criteria instance to use
     * @param Request $request The request containing filter and sort parameters
     */
    public function __construct(Criteria $criteria, Request $request)
    {
        $this->criteria = $criteria;
        $this->queryParameters = $request->all();
    }

    /**
     * Returns an array of available filters.
     * Override this method to define available filters.
     *
     * @return Filter[]|string[] Array of filter instances or method names
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * Returns an array of available sorts.
     * Override this method to define available sorts.
     *
     * @return string[] Array of sort field names
     */
    protected function sorts(): array
    {
        return [];
    }

    /**
     * Gets the current Criteria instance.
     *
     * @return Criteria The current criteria instance
     */
    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    /**
     * Initializes a fresh Criteria instance and resets the internal state.
     *
     * @return $this
     */
    public function fresh(): UtilitiesService
    {
        $this->criteria = new Criteria;
        return $this;
    }

    /**
     * Applies all valid filters from the request to the criteria.
     *
     * @return $this
     */
    final public function applyFilters(): UtilitiesService
    {
        foreach ($this->getApplicableFilters() as $filterKey => $filter) {
            $this->applyFilter($filterKey, $filter);
        }
        return $this;
    }

    /**
     * Gets the list of filters that have values in the request.
     *
     * @return array Array of applicable filters
     */
    private function getApplicableFilters(): array
    {
        return array_filter(
            $this->filters(),
            fn($filterKey) => $this->hasFilterValue($filterKey),
            ARRAY_FILTER_USE_KEY
        );
    }
    /**
     * Checks if a filter has a non-empty value in the request.
     *
     * @param string $filterKey The filter key to check
     * @return bool True if the filter has a value, false otherwise
     */
    private function hasFilterValue(string $filterKey): bool
    {
        return isset($this->queryParameters[$filterKey])
            && !empty($this->queryParameters[$filterKey]);
    }

    /**
     * Applies a filter to the criteria.
     *
     * @param string $filterKey The key of the filter
     * @param string|Filter $filter The filter to apply
     */
    private function applyFilter(string $filterKey, string|Filter $filter): void
    {
        $value = $this->queryParameters[$filterKey];

        if ($filter instanceof Filter) {
            $filter->apply($this->criteria, $value);
        } elseif (method_exists($this, $filter)) {
            $this->$filter($this->criteria, $value);
        }
    }

    /**
     * Applies all valid sorts from the request to the criteria.
     *
     * @return $this
     */
    final public function applySorts(): UtilitiesService
    {
        if (!$this->hasValidSorts()) {
            return $this;
        }
        foreach ($this->getValidSortParameters() as $sort) {
            $this->applySort($sort);
        }
        return $this;
    }

    /**
     * Checks if there are valid sorts in the request.
     *
     * @return bool True if there are valid sorts, false otherwise
     */
    private function hasValidSorts(): bool
    {
        return !empty($this->queryParameters[$this->sortsKey] ?? []) && !empty($this->availableSorts());
    }

    /**
     * Gets the list of available sorts, using cached value if available.
     *
     * @return array Array of available sorts
     */
    private function availableSorts(): array
    {
        if (isset($this->availableSorts)) {
            return $this->availableSorts;
        }
        return $this->availableSorts = $this->sorts();
    }

    /**
     * Gets the list of valid sort parameters from the request.
     *
     * @return array Array of valid sort parameters
     */
    private function getValidSortParameters(): array
    {
        return array_filter(
            $this->queryParameters[$this->sortsKey],
            fn($sort) => $this->isValidSortParameter($sort)
        );
    }

    /**
     * Checks if a sort parameter is valid.
     *
     * @param array $sort The sort parameter to check
     * @return bool True if the sort parameter is valid, false otherwise
     */
    private function isValidSortParameter(array $sort): bool
    {
        $field = $sort['field'] ?? null;
        return $field && isset($this->availableSorts()[$field]);
    }

    /**
     * Applies a sort to the criteria.
     *
     * @param array $sort The sort parameters to apply
     */
    private function applySort(array $sort): void
    {
        $field = $sort['field'];
        $direction = $this->getSortDirection($sort);
        $resolvedField = $this->availableSorts()[$field];

        $this->criteria->appendSort(
            $this->createSortObject($resolvedField, $direction)
        );
    }

    /**
     * Gets the sort direction from the sort parameters or falls back to default.
     *
     * @param array $sort The sort parameters
     * @return string The sort direction
     */
    private function getSortDirection(array $sort): string
    {
        return match (true) {
            $this->isValidDirection($sort['direction'] ?? null) => strtoupper($sort['direction']),
            $this->isValidDirection($this->defaultSortDirection ?? null) => strtoupper($this->defaultSortDirection),
            default => SortDirectionEnum::default()
        };
    }

    /**
     * Checks if a direction is valid.
     *
     * @param string|null $direction The direction to check
     * @return bool True if the direction is valid, false otherwise
     */
    private function isValidDirection(?string $direction): bool
    {
        return $direction !== null && in_array(strtoupper($direction), SortDirectionEnum::values());
    }

    /**
     * Creates a sort object for the given field and direction.
     *
     * @param string $resolvedSortField The resolved sort field
     * @param string $direction The sort direction
     * @return BaseSort The created sort object
     */
    private function createSortObject(string $resolvedSortField, string $direction): BaseSort
    {
        return method_exists($this, $resolvedSortField)
            ? $this->$resolvedSortField($direction)
            : new Sort($resolvedSortField, $direction);
    }
}