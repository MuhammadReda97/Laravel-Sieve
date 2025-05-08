<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\BetweenCondition;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\Condition;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\GroupConditions;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\InCondition;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\NullCondition;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\RawCondition;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\WhenCondition;
use Tests\TestCase;

class WhenConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $verification = true;
        $condition = new Condition('field', '=', 'value');

        $whenCondition = new WhenCondition($verification, $condition);

        $this->assertEquals($verification, $whenCondition->verification);
        $this->assertEquals($condition, $whenCondition->condition);
        $this->assertEquals('and', $whenCondition->boolean);
    }

    public function test_apply_executes_condition_when_verification_is_true(): void
    {
        $verification = true;
        $condition = new Condition('field', '=', 'value');

        $whenCondition = new WhenCondition($verification, $condition);

        $this->mockedBuilder->expects($this->once())
            ->method('when')
            ->with($verification, $this->callback(function ($callback) {
                $mockBuilder = $this->createMock(Builder::class);
                $mockBuilder->expects($this->once())
                    ->method('where')
                    ->with('field', '=', 'value');
                $callback($mockBuilder);
                return true;
            }));

        $whenCondition->apply($this->mockedBuilder);
    }

    public function test_apply_does_not_execute_condition_when_verification_is_false(): void
    {
        $verification = false;
        $condition = $this->createMock(Condition::class);

        $condition->expects($this->never())->method('apply');

        $this->mockedBuilder->expects($this->once())->method('when');

        $whenCondition = new WhenCondition($verification, $condition);
        $whenCondition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_condition_types(): void
    {
        $verification = true;
        $testCases = [
            'basic condition' => new Condition('field', '=', 'value'),
            'null condition' => new NullCondition('field'),
            'in condition' => new InCondition('field', ['value1', 'value2']),
            'between condition' => new BetweenCondition('field', [1, 2]),
            'raw condition' => new RawCondition('field > ?', [100]),
            'grouped condition' => new GroupConditions([
                new Condition('field1', '=', 'value1', 'or'),
                new Condition('field2', '!=', 'value2', 'or')
            ])
        ];

        foreach ($testCases as $condition) {
            $this->mockedBuilder->expects($this->once())
                ->method('when')
                ->with($verification, $this->callback(function ($callback) use ($condition) {
                    $mockBuilder = $this->createMock(Builder::class);
                    $condition->apply($mockBuilder);
                    $callback($mockBuilder);
                    return true;
                }));

            $whenCondition = new WhenCondition($verification, $condition);
            $whenCondition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_verification(): void
    {
        $this->expectException(\TypeError::class);
        new WhenCondition(null, new Condition('field', '=', 'value'));
    }

    public function test_constructor_throws_exception_for_null_condition(): void
    {
        $this->expectException(\TypeError::class);
        new WhenCondition(true, null);
    }
} 