<?php

namespace RedaLabs\LaravelFilters\Filters;

use Exception;
use RedaLabs\LaravelFilters\Criteria;
use RedaLabs\LaravelFilters\Exceptions\Operators\InvalidOperatorException;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\Condition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\DateCondition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\GroupConditions;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\InCondition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\JsonContainCondition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\JsonContainsKeyCondition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\NullCondition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\WhenCondition;
use RedaLabs\LaravelFilters\Filters\Contracts\Filter;
use RedaLabs\LaravelFilters\Filters\Joins\Concretes\ClosureJoin;
use RedaLabs\LaravelFilters\Filters\Joins\Concretes\Join;

class UsersFilter implements Filter
{
    protected string $field = 'users.name';

    /**
     * @throws InvalidOperatorException
     * @throws Exception
     */
    public function apply(Criteria $criteria, mixed $value): void
    {
        $criteria
            ->appendJoin(new ClosureJoin('user_info', function ($join) {
                $join->on('users.id', '=', 'user_info.user_id')
                    ->where('user_info.active', '=', 1);
            }, 'left'), 1)
            ->appendJoin(new Join('activity_logs', 'activity_logs.user_id', '=', 'users.id', 'left'), 3)
            ->appendJoin(new Join('orders', 'orders.user_id', '=', 'users.id', 'left')
                ->appendCondition(new Condition('orders.status', '=', 'active'))
                ->appendCondition(new GroupConditions([
                    new NullCondition('orders.confirmed', not: true),
                    new DateCondition('orders.date', '>=', '2023-01-01', 'or')
                ]))
                , 2)
            ->appendCondition(new JsonContainsKeyCondition('activity_logs.data->bad_key', not: true))
            ->appendCondition(new JsonContainsKeyCondition('activity_logs.data->good_key'))
            ->appendCondition(new JsonContainCondition('activity_logs.data->property', 'good'))
            ->appendCondition(new JsonContainCondition('activity_logs.data->property', 'bad', not: true))
            ->appendCondition(new InCondition('orders.status', ['active', 'pending']))
            ->appendCondition(new InCondition('orders.status', ['rejected'], not: true))




//            ->appendJoin(
//                new NormalJoin('users', 'users.id', '=', 'table.user_id', 'left')
//                    ->appendCondition(new NotNullCondition('users.id'))
//                    ->appendCondition(new InCondition('users.id', [1, 2, 3, 4, 5]))
//            )
//            ->appendJoin(new ClosureJoin('clousra', function ($join) {
//                $join->on('users.id', '=', 'clousra.user_id')
//                    ->where('users.id', '!=', 1);
//            }, 'right'), 1)
//            ->appendJoin(
//                new ClosureJoin('users', function ($query) {
//                    $query->where('users.id', '=', 1);
//                }, 'left')
//            )
//            ->appendCondition(new Condition($this->field, 'like', "%{$value}%"))
//            ->appendCondition(new AggregationCondition('salary', '>', 1000))
//            ->appendCondition(new InCondition('in', [1, 2, 3, 4, 5]))
//            ->appendCondition(new NotInCondition('not_in', [1, 2, 3, 4, 5]))
//            ->appendCondition(new NullCondition('null'))
//            ->appendCondition(new NotNullCondition('not_null'))
//            ->appendCondition(new RawCondition('raw > ?', [1000]))
//            ->appendCondition(new BetweenCondition('between', [1000, 2000]))
//            ->appendCondition(new DateCondition('date', '>=', '2023-01-01'))
//            ->appendCondition(new WhenCondition(true, new Condition('when', '=', 1)))
//            ->appendCondition(new JsonContainCondition('json->property', 'query'))
            ->appendCondition(new GroupConditions([
                new GroupConditions([
                    new Condition('group1', '=', 1),
                    new Condition('group2', '=', 2),
                ], 'or'),

                new GroupConditions([
                    new Condition('group3', '=', 3),
                    new Condition('group4', '=', 4),
                ], 'or'),

                new WhenCondition(true, new GroupConditions([
                    new WhenCondition(true, new Condition('when', '=', 10)),
                    new Condition('group5', '=', 5)
                ], 'or'))

            ]))
            ->appendCondition(new WhenCondition(true, new Condition('main_when', '=', 1, 'or')));

    }
}
