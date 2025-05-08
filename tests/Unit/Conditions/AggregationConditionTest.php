<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Conditions\LogicalOperatorEnum;
use ArchiTools\LaravelSieve\Enums\Operators\OperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Conditions\InvalidLogicalOperatorException;
use ArchiTools\LaravelSieve\Exceptions\Operators\InvalidOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\AggregationCondition;
use Tests\TestCase;

class AggregationConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $field = 'count';
        $operator = '>';
        $value = 10;
        $boolean = 'and';

        $condition = new AggregationCondition($field, $operator, $value, $boolean);

        $this->assertEquals($field, $condition->field);
        $this->assertEquals($operator, $condition->operator);
        $this->assertEquals($value, $condition->value);
        $this->assertEquals($boolean, $condition->boolean);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new AggregationCondition('count', '>', 10);

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_not_accept_invalid_operator(): void
    {
        $this->expectException(InvalidOperatorException::class);
        new AggregationCondition('count', 'INVALID', 10);
    }

    public function test_condition_not_accept_invalid_logical_operator(): void
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new AggregationCondition('count', '>', 10, 'INVALID');
    }

    public function test_condition_accepts_only_valid_operators(): void
    {
        $validOperators = OperatorEnum::values();
        $field = 'count';
        $value = 10;

        foreach ($validOperators as $operator) {
            try {
                new AggregationCondition($field, $operator, $value);
                $this->addToAssertionCount(1);
            } catch (InvalidOperatorException $e) {
                $this->fail("Operator {$operator} should be valid but threw exception");
            }
        }
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $field = 'count';
        $value = 10;
        $operator = '>';

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new AggregationCondition($field, $operator, $value, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$operator} should be valid but threw exception");
            }
        }
    }

    public function test_apply_adds_having_clause_to_builder(): void
    {
        $field = 'count';
        $operator = '>';
        $value = 10;
        $boolean = 'and';

        $condition = new AggregationCondition($field, $operator, $value, $boolean);

        $this->mockedBuilder->expects($this->once())
            ->method('having')
            ->with($field, $operator, $value, $boolean);

        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $field = 'count';
        $operator = '>';
        $value = 10;

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('having')
                ->with($field, $operator, $value, $boolean);

            $condition = new AggregationCondition(
                $field,
                $operator,
                $value,
                $boolean
            );

            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_apply_works_with_different_value_types(): void
    {
        $field = 'sum';
        $operator = '>';

        $testCases = [
            'integer value' => 25,
            'string value' => '25',
            'null value' => null,
            'boolean value' => true,
            'float value' => 25.5,
        ];

        foreach ($testCases as $description => $value) {
            $this->mockedBuilder->expects($this->once())
                ->method('having')
                ->with($field, $operator, $value);

            $condition = new AggregationCondition($field, $operator, $value);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_field(): void
    {
        $this->expectException(\TypeError::class);
        new AggregationCondition(null, '>', 10);
    }

    public function test_apply_handles_empty_field_name(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('having')
            ->with('', '>', 10);

        $condition = new AggregationCondition('', '>', 10);
        $condition->apply($this->mockedBuilder);
    }
} 