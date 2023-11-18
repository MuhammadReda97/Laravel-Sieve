<?php

namespace SortifyLoom\Utilities;

use SortifyLoom\Utilities\Enums\Units\SortDirectionEnum;
use SortifyLoom\Utilities\Filters\Abstractions\Filter;
use SortifyLoom\Utilities\Filters\Units\Criteria;
use SortifyLoom\Utilities\Sorts\Units\BasicSort;
use Illuminate\Support\Str;

abstract class UtilitiesService
{
    private Criteria $criteria;
    protected string $sortKey = 'sorts';

    public function __construct(Criteria $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @return Criteria
     */
    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    /**
     * @return Filter[]
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
         * @var Filter $filterClass
         */
        foreach ($this->filters() as $filterClass) {
            if (!isset($filterClass::info()['key'])) {
                continue;
            }
            if (request()->filled($filterKey = $filterClass::info()['key']) || 1) {
                $filterClass::filter($this->criteria, request()->input($filterKey));
            }
        }
        return $this;
    }

    final public function applySorts(): UtilitiesService
    {
        if (!request()->filled($this->sortKey) || !is_array(request()->input($this->sortKey))) {
            return $this;
        }

        foreach (request()->input($this->sortKey) as $sort) {
            if (!isset(array_combine($this->sorts(), $this->sorts())[$sort['field']]))
                continue;

            $sortMethodName = Str::camel($sort['field']) . 'Sorting';
            if (method_exists($this, $sortMethodName)) {
                $this->criteria->appendSort($this->$sortMethodName());
            } elseif (method_exists($this, 'sortMapping') && isset($this->sortMapping()[$sort['field']])) {
                $this->criteria->appendSort(
                    new BasicSort(
                        $this->sortMapping()[$sort['field']],
                        $sort['direction'] ?? SortDirectionEnum::default()
                    )
                );
            } else {
                $this->criteria->appendSort(new BasicSort($sort['field'], $sort['direction'] ?? SortDirectionEnum::default()));
            }
        }
        return $this;
    }
}