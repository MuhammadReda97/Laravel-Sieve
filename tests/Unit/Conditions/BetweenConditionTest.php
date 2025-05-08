<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Conditions\LogicalOperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Conditions\InvalidLogicalOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\BetweenCondition;
use Tests\TestCase;

class BetweenConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $field = 'age';
        $values = [18, 30];
        $boolean = 'and';
        $not = false;

        $condition = new BetweenCondition($field, $values, $boolean, $not);

        $this->assertEquals($field, $condition->field);
        $this->assertEquals($values, $condition->values);
        $this->assertEquals($boolean, $condition->boolean);
        $this->assertEquals($not, $condition->not);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new BetweenCondition('age', [18, 30]);

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_uses_default_not_when_not_provided(): void
    {
        $condition = new BetweenCondition('age', [18, 30]);

        $this->assertFalse($condition->not);
    }

    public function test_condition_not_accept_invalid_logical_operator(): void
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new BetweenCondition('age', [18, 30], 'INVALID');
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $field = 'age';
        $values = [18, 30];

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new BetweenCondition($field, $values, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$logicalOperator} should be valid but threw exception");
            }
        }
    }

    public function test_condition_requires_exactly_two_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Between condition requires exactly two values.');

        new BetweenCondition('age', [18]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Between condition requires exactly two values.');

        new BetweenCondition('age', [18, 22, 30]);
    }

    public function test_apply_adds_where_between_clause_to_builder(): void
    {
        $field = 'age';
        $values = [18, 30];
        $boolean = 'and';
        $not = false;

        $condition = new BetweenCondition($field, $values, $boolean, $not);

        $this->mockedBuilder->expects($this->once())
            ->method('whereBetween')
            ->with($field, $values, $boolean, $not);

        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $field = 'age';
        $values = [18, 30];

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereBetween')
                ->with($field, $values, $boolean, false);

            $condition = new BetweenCondition($field, $values, $boolean);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_apply_works_with_not_parameter(): void
    {
        $field = 'age';
        $values = [18, 30];
        $boolean = 'and';

        // Test with not = true
        $this->mockedBuilder->expects($this->once())
            ->method('whereBetween')
            ->with($field, $values, $boolean, true);

        $condition = new BetweenCondition($field, $values, $boolean, true);
        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_value_types(): void
    {
        $field = 'age';
        $testCases = [
            'integer values' => [18, 30],
            'string values' => ['18', '30'],
            'float values' => [18.5, 30.5],
            'mixed values' => [18, '30'],
        ];

        foreach ($testCases as $values) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereBetween')
                ->with($field, $values);

            $condition = new BetweenCondition($field, $values);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_field(): void
    {
        $this->expectException(\TypeError::class);
        new BetweenCondition(null, [18, 30]);
    }

    public function test_apply_handles_empty_field_name(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('whereBetween')
            ->with('', [18, 30]);

        $condition = new BetweenCondition('', [18, 30]);
        $condition->apply($this->mockedBuilder);
    }
} 