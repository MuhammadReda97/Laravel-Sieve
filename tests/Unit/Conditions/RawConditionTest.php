<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use RedaLabs\LaravelFilters\Enums\Conditions\LogicalOperatorEnum;
use RedaLabs\LaravelFilters\Exceptions\Conditions\InvalidLogicalOperatorException;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\RawCondition;
use Tests\TestCase;

class RawConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $expression = 'price > ?';
        $bindings = [100];
        $boolean = 'and';

        $condition = new RawCondition($expression, $bindings, $boolean);

        $this->assertEquals($expression, $condition->expression);
        $this->assertEquals($bindings, $condition->bindings);
        $this->assertEquals($boolean, $condition->boolean);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new RawCondition('price > ?', [100]);

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_uses_default_empty_bindings_when_not_provided(): void
    {
        $condition = new RawCondition('price > 100');

        $this->assertEquals([], $condition->bindings);
    }

    public function test_condition_not_accept_invalid_logical_operator(): void
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new RawCondition('price > ?', [100], 'INVALID');
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $expression = 'price > ?';
        $bindings = [100];

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new RawCondition($expression, $bindings, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$logicalOperator} should be valid but threw exception");
            }
        }
    }

    public function test_apply_adds_where_raw_clause_to_builder(): void
    {
        $expression = 'price > ?';
        $bindings = [100];
        $boolean = 'and';

        $condition = new RawCondition($expression, $bindings, $boolean);

        $this->mockedBuilder->expects($this->once())
            ->method('whereRaw')
            ->with($expression, $bindings, $boolean);

        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $expression = 'price > ?';
        $bindings = [100];

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereRaw')
                ->with($expression, $bindings, $boolean);

            $condition = new RawCondition($expression, $bindings, $boolean);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_apply_works_with_different_binding_types(): void
    {
        $expression = 'price > ? AND status = ?';
        $testCases = [
            'integer bindings' => [100, 1],
            'string bindings' => ['100', 'active'],
            'mixed bindings' => [100, 'active'],
            'empty bindings' => [],
        ];

        foreach ($testCases as $bindings) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereRaw')
                ->with($expression, $bindings);

            $condition = new RawCondition($expression, $bindings);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_expression(): void
    {
        $this->expectException(\TypeError::class);
        new RawCondition(null, [100]);
    }

    public function test_apply_handles_empty_expression(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('whereRaw')
            ->with('', []);

        $condition = new RawCondition('', []);
        $condition->apply($this->mockedBuilder);
    }
} 