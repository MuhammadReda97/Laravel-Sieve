<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Conditions\LogicalOperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Conditions\InvalidLogicalOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\InCondition;
use Tests\TestCase;

class InConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $field = 'status';
        $values = ['active', 'pending'];
        $boolean = 'and';
        $not = false;

        $condition = new InCondition($field, $values, $boolean, $not);

        $this->assertEquals($field, $condition->field);
        $this->assertEquals($values, $condition->values);
        $this->assertEquals($boolean, $condition->boolean);
        $this->assertEquals($not, $condition->not);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new InCondition('status', ['active', 'pending']);

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_uses_default_not_when_not_provided(): void
    {
        $condition = new InCondition('status', ['active', 'pending']);

        $this->assertFalse($condition->not);
    }

    public function test_condition_not_accept_invalid_logical_operator(): void
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new InCondition('status', ['active', 'pending'], 'INVALID');
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $field = 'status';
        $values = ['active', 'pending'];

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new InCondition($field, $values, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$logicalOperator} should be valid but threw exception");
            }
        }
    }

    public function test_apply_adds_where_in_clause_to_builder(): void
    {
        $field = 'status';
        $values = ['active', 'pending'];
        $boolean = 'and';
        $not = false;

        $condition = new InCondition($field, $values, $boolean, $not);

        $this->mockedBuilder->expects($this->once())
            ->method('whereIn')
            ->with($field, $values, $boolean, $not);

        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $field = 'status';
        $values = ['active', 'pending'];

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereIn')
                ->with($field, $values, $boolean, false);

            $condition = new InCondition($field, $values, $boolean);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_apply_works_with_not_parameter(): void
    {
        $field = 'status';
        $values = ['active', 'pending'];
        $boolean = 'and';

        // Test with not = true
        $this->mockedBuilder->expects($this->once())
            ->method('whereIn')
            ->with($field, $values, $boolean, true);

        $condition = new InCondition($field, $values, $boolean, true);
        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_value_types(): void
    {
        $field = 'status';
        $testCases = [
            'string values' => ['active', 'pending'],
            'integer values' => [1, 2, 3],
            'mixed values' => ['active', 1, true],
            'empty array' => [],
        ];

        foreach ($testCases as $description => $values) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereIn')
                ->with($field, $values);

            $condition = new InCondition($field, $values);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_field(): void
    {
        $this->expectException(\TypeError::class);
        new InCondition(null, ['active', 'pending']);
    }

    public function test_apply_handles_empty_field_name(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('whereIn')
            ->with('', ['active', 'pending']);

        $condition = new InCondition('', ['active', 'pending']);
        $condition->apply($this->mockedBuilder);
    }
} 