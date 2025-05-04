<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use RedaLabs\LaravelFilters\Enums\Conditions\GroupConditionTypeEnum;
use RedaLabs\LaravelFilters\Exceptions\Conditions\EmptyGroupConditionsException;
use RedaLabs\LaravelFilters\Exceptions\Conditions\MixedGroupConditionException;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\AggregationCondition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\Condition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\DateCondition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\GroupConditions;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\NullCondition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\WhenCondition;
use Tests\TestCase;

class GroupConditionsTest extends TestCase
{
    /** @test */
    public function it_throws_exception_for_empty_conditions()
    {
        $this->expectException(EmptyGroupConditionsException::class);
        new GroupConditions([]);
    }

    /** @test */
    public function it_throws_exception_for_mixed_basic_and_aggregation_conditions()
    {
        $this->expectException(MixedGroupConditionException::class);

        new GroupConditions([
            $this->createMock(Condition::class),
            $this->createMock(AggregationCondition::class)
        ]);
    }

    /** @test */
    public function it_accepts_all_basic_conditions()
    {
        $group = new GroupConditions([
            $this->createMock(Condition::class),
            $this->createMock(NullCondition::class),
            $this->createMock(DateCondition::class)
        ]);

        $this->assertEquals(GroupConditionTypeEnum::BASIC->value, $group->type);
    }

    /** @test */
    public function it_accepts_all_aggregation_conditions()
    {
        $group = new GroupConditions([
            $this->createMock(AggregationCondition::class),
            $this->createMock(AggregationCondition::class)
        ]);

        $this->assertEquals(GroupConditionTypeEnum::AGGREGATION->value, $group->type);
    }

    /** @test */
    public function it_accepts_nested_group_conditions()
    {
        $nestedGroup = new GroupConditions([
            $this->createMock(Condition::class),
            $this->createMock(Condition::class)
        ]);

        $group = new GroupConditions([
            $nestedGroup,
            $this->createMock(Condition::class)
        ]);

        $this->assertEquals(GroupConditionTypeEnum::BASIC->value, $group->type);
    }

    /** @test */
    public function it_accepts_when_conditions_inside_groups()
    {
        $group = new GroupConditions([
            new WhenCondition(true, $this->createMock(Condition::class)),
            $this->createMock(Condition::class)
        ]);

        $this->assertEquals(GroupConditionTypeEnum::BASIC->value, $group->type);
    }

    /** @test */
    public function it_applies_basic_conditions_with_where_clause()
    {
        $builder = $this->createMock(Builder::class);
        $condition1 = $this->createMock(Condition::class);
        $condition2 = $this->createMock(NullCondition::class);

        $group = new GroupConditions([$condition1, $condition2]);

        $builder->expects($this->once())
            ->method('where')
            ->with(
                $this->callback(function ($callback) use ($condition1, $condition2) {
                    $mockBuilder = $this->createMock(Builder::class);

                    $condition1->expects($this->once())
                        ->method('apply')
                        ->with($mockBuilder);

                    $condition2->expects($this->once())
                        ->method('apply')
                        ->with($mockBuilder);

                    $callback($mockBuilder);
                    return true;
                }),
                null,
                null,
                'and'
            );

        $group->apply($builder);
    }

    /** @test */
    public function it_applies_aggregation_conditions_with_having_clause()
    {
        $builder = $this->createMock(Builder::class);
        $condition1 = $this->createMock(AggregationCondition::class);
        $condition2 = $this->createMock(AggregationCondition::class);

        $group = new GroupConditions([$condition1, $condition2]);

        $builder->expects($this->once())
            ->method('having')
            ->with(
                $this->callback(function ($callback) use ($condition1, $condition2) {
                    $mockBuilder = $this->createMock(Builder::class);

                    $condition1->expects($this->once())
                        ->method('apply')
                        ->with($mockBuilder);

                    $condition2->expects($this->once())
                        ->method('apply')
                        ->with($mockBuilder);

                    $callback($mockBuilder);
                    return true;
                }),
                null,
                null,
                'and'
            );

        $group->apply($builder);
    }

    /** @test */
    public function it_uses_custom_boolean_operator()
    {
        $builder = $this->createMock(Builder::class);
        $condition = $this->createMock(Condition::class);

        $group = new GroupConditions([$condition], 'or');

        $builder->expects($this->once())
            ->method('where')
            ->with(
                $this->anything(),
                null,
                null,
                'or'
            );

        $group->apply($builder);
    }

    /** @test */
    public function it_handles_nested_group_conditions_correctly()
    {
        $builder = $this->createMock(Builder::class);

        $nestedCondition1 = $this->createMock(Condition::class);
        $nestedCondition2 = $this->createMock(Condition::class);
        $topLevelCondition = $this->createMock(Condition::class);

        $nestedGroup = new GroupConditions([$nestedCondition1, $nestedCondition2], 'or');
        $group = new GroupConditions([$nestedGroup, $topLevelCondition]);

        $builder->expects($this->once())
            ->method('where')
            ->with(
                $this->callback(function ($callback) use ($nestedCondition1, $nestedCondition2, $topLevelCondition) {
                    $mockBuilder = $this->createMock(Builder::class);

                    $mockBuilder->expects($this->once())
                        ->method('where')
                        ->with(
                            $this->callback(function ($nestedCallback) use ($nestedCondition1, $nestedCondition2) {
                                $nestedMockBuilder = $this->createMock(Builder::class);

                                $nestedCondition1->expects($this->once())
                                    ->method('apply')
                                    ->with($nestedMockBuilder);

                                $nestedCondition2->expects($this->once())
                                    ->method('apply')
                                    ->with($nestedMockBuilder);

                                $nestedCallback($nestedMockBuilder);
                                return true;
                            }),
                            null,
                            null,
                            'or'
                        );

                    $topLevelCondition->expects($this->once())
                        ->method('apply')
                        ->with($mockBuilder);

                    $callback($mockBuilder);
                    return true;
                }),
                null,
                null,
                'and'
            );

        $group->apply($builder);
    }

    /** @test */
    public function it_handles_complex_structure_example()
    {
        $builder = $this->createMock(Builder::class);

        $group = new GroupConditions([
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
        ]);

        $builder->expects($this->once())
            ->method('where')
            ->with(
                $this->callback(function ($callback) {
                    $mockBuilder = $this->createMock(Builder::class);

                    $mockBuilder->expects($this->exactly(2))
                        ->method('where')
                        ->with(
                            $this->callback(function ($nestedCallback) {
                                $nestedMock = $this->createMock(Builder::class);
                                $nestedMock->expects($this->exactly(2))
                                    ->method('where');
                                $nestedCallback($nestedMock);
                                return true;
                            }),
                            null,
                            null,
                            'or'
                        );

                    $mockBuilder->expects($this->once())
                        ->method('when')
                        ->with(
                            true,
                            $this->callback(function ($whenCallback) {
                                $whenMock = $this->createMock(Builder::class);

                                $whenMock->expects($this->once())
                                    ->method('where')
                                    ->with(
                                        $this->callback(function ($nestedWhenCallback) {
                                            $nestedWhenMock = $this->createMock(Builder::class);

                                            $nestedWhenMock->expects($this->once())
                                                ->method('when')
                                                ->with(true, $this->anything());

                                            $nestedWhenMock->expects($this->once())
                                                ->method('where');

                                            $nestedWhenCallback($nestedWhenMock);
                                            return true;
                                        }),
                                        null,
                                        null,
                                        'or'
                                    );

                                $whenCallback($whenMock);
                                return true;
                            }
                            ));

                    $callback($mockBuilder);
                    return true;
                }),
                null,
                null,
                'and'
            );

        $group->apply($builder);
    }
}