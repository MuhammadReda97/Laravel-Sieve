<?php

namespace Tests\Unit\Core;

use RedaLabs\LaravelFilters\Criteria;
use RedaLabs\LaravelFilters\Exceptions\Operators\InvalidOperatorException;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\Condition;
use RedaLabs\LaravelFilters\Sorts\Concretes\Sort;
use RedaLabs\LaravelFilters\UtilitiesService;

class ConcreteUtilitiesService extends UtilitiesService
{
    protected function filters(): array
    {
        return [
            'name' => new NameFilter('users.name'),
            'age' => 'ageFilter'
        ];
    }

    protected function sorts(): array
    {
        return [
            'name' => 'customNameSort',
            'created_at' => 'created_at'
        ];
    }

    /**
     * @throws InvalidOperatorException
     */
    protected function ageFilter(Criteria $criteria, string $value): void
    {
        $criteria->appendCondition(new Condition('age', '>=', $value));
    }

    protected function customNameSort(string $direction): Sort
    {
        return new Sort('name', $direction);
    }
}
