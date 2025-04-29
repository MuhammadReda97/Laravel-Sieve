<?php

namespace RedaLabs\LaravelFilters\Utilities;

use Illuminate\Http\Request;
use RedaLabs\LaravelFilters\Utilities\Enums\Sorts\SortDirectionEnum;
use RedaLabs\LaravelFilters\Utilities\Filters\Abstractions\Filter;
use RedaLabs\LaravelFilters\Utilities\Filters\Units\Criteria;
use RedaLabs\LaravelFilters\Utilities\Sorts\Abstractions\Sort;
use RedaLabs\LaravelFilters\Utilities\Sorts\Units\BasicSort;

abstract class UtilitiesService
{
    protected string $defaultSortDirection;

    protected string $sortsKey = 'sorts';

    private Criteria $criteria;

    private array $queryParameters = [];

    private array $availableSorts;

    public function __construct(Criteria $criteria, Request $request)
    {
        $this->criteria = $criteria;
        $this->queryParameters = $request->all();
    }

    /**
     * @return Criteria
     */
    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    /**
     * @return Filter[] | string[]
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    protected function sorts(): array
    {
        return [];
    }

    final public function applyFilters(): UtilitiesService
    {
        /**
         * @var Filter|string $filter
         */
        foreach ($this->getApplicableFilters() as $filterKey => $filter) {
            $this->applyFilter($filterKey, $filter);
        }
        return $this;

    }

    final public function applySorts(): self
    {
        if (!$this->hasValidSorts()) {
            return $this;
        }
        foreach ($this->getValidSortParameters() as $sort) {
            $this->applySort($sort);
        }
        return $this;
    }

    private function availableSorts(): array
    {
        if (isset($this->availableSorts)) {
            return $this->availableSorts;
        }
        return $this->availableSorts = $this->sorts();
    }

    private function getApplicableFilters(): array
    {
        return array_filter(
            $this->filters(),
            fn($filterKey) => $this->hasFilterValue($filterKey),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function hasFilterValue(string $filterKey): bool
    {
        return isset($this->queryParameters[$filterKey])
            && !empty($this->queryParameters[$filterKey]);
    }

    private function applyFilter(string $filterKey, string|Filter $filter): void
    {
        $value = $this->queryParameters[$filterKey];

        if ($filter instanceof Filter) {
            $filter->filter($this->criteria, $value);
        } elseif (method_exists($this, $filter)) {
            $this->$filter($this->criteria, $value);
        }
    }

    private function hasValidSorts(): bool
    {
        return !empty($this->queryParameters[$this->sortsKey] ?? []) && !empty($this->availableSorts());
    }

    private function getValidSortParameters(): array
    {
        return array_filter(
            $this->queryParameters[$this->sortsKey],
            fn($sort) => $this->isValidSortParameter($sort)
        );
    }

    private function isValidSortParameter(array $sort): bool
    {
        $field = $sort['field'] ?? null;
        return $field && isset($this->availableSorts()[$field]);
    }

    private function applySort(array $sort): void
    {
        $field = $sort['field'];
        $direction = $this->getSortDirection($sort);
        $resolvedField = $this->availableSorts()[$field];

        $this->criteria->appendSort(
            $this->createSortObject($resolvedField, $direction)
        );
    }

    private function getSortDirection(array $sort): string
    {
        return match (true) {
            $this->isValidDirection($sort['direction'] ?? null) => strtoupper($sort['direction']),
            $this->isValidDirection($this->defaultSortDirection ?? null) => strtoupper($this->defaultSortDirection),
            default => SortDirectionEnum::default()
        };
    }

    private function isValidDirection(?string $direction): bool
    {
        return $direction !== null && in_array(strtoupper($direction), SortDirectionEnum::values());
    }

    private function createSortObject(string $resolvedSortField, string $direction): Sort
    {
        return method_exists($this, $resolvedSortField)
            ? $this->$resolvedSortField($direction)
            : new BasicSort($resolvedSortField, $direction);
    }
}