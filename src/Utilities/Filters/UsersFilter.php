<?php

namespace SortifyLoom\Utilities\Filters;

use SortifyLoom\Utilities\Filters\Abstractions\Filter;
use SortifyLoom\Utilities\Filters\Units\Conditions\AggregationCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\Condition;
use SortifyLoom\Utilities\Filters\Units\Conditions\InCondition;
use SortifyLoom\Utilities\Filters\Units\Conditions\NotNullCondition;
use SortifyLoom\Utilities\Filters\Units\Criteria;
use SortifyLoom\Utilities\Filters\Units\Joins\NormalJoin;

class UsersFilter extends Filter
{
    protected string $field = 'users.id';

    public static function filter(Criteria $criteria, mixed $value): void
    {
        $criteria
            ->appendJoin(
                (new NormalJoin('users', 'users.id', 'table.user_id', 'left'))
                    ->appendCondition(new NotNullCondition('users.id'))
                    ->appendCondition(new InCondition('users.id', [1, 2, 3, 4, 5]))
            )
            ->appendCondition(new Condition('users.name', 'like', "%{$value}%"))
            ->appendCondition(new AggregationCondition('salary', '>', 1000));
    }
}
