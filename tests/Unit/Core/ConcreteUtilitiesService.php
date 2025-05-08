<?php

namespace Tests\Unit\Core;

use ArchiTools\LaravelSieve\Criteria;
use ArchiTools\LaravelSieve\Exceptions\Operators\InvalidOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\Condition;
use ArchiTools\LaravelSieve\Sorts\Concretes\Sort;
use ArchiTools\LaravelSieve\UtilitiesService;

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
