<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Conditions\LogicalOperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Conditions\InvalidLogicalOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\NullCondition;
use Tests\TestCase;

class NullConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $field = 'deleted_at';
        $boolean = 'and';
        $not = false;

        $condition = new NullCondition($field, $boolean, $not);

        $this->assertEquals($field, $condition->field);
        $this->assertEquals($boolean, $condition->boolean);
        $this->assertEquals($not, $condition->not);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new NullCondition('deleted_at');

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_uses_default_not_when_not_provided(): void
    {
        $condition = new NullCondition('deleted_at');

        $this->assertFalse($condition->not);
    }

    public function test_condition_not_accept_invalid_logical_operator(): void
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new NullCondition('deleted_at', 'INVALID');
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $field = 'deleted_at';

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new NullCondition($field, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$logicalOperator} should be valid but threw exception");
            }
        }
    }

    public function test_apply_adds_where_null_clause_to_builder(): void
    {
        $field = 'deleted_at';
        $boolean = 'and';
        $not = false;

        $condition = new NullCondition($field, $boolean, $not);

        $this->mockedBuilder->expects($this->once())
            ->method('whereNull')
            ->with($field, $boolean, $not);

        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $field = 'deleted_at';

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereNull')
                ->with($field, $boolean, false);

            $condition = new NullCondition($field, $boolean);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_apply_works_with_not_parameter(): void
    {
        $field = 'deleted_at';
        $boolean = 'and';

        // Test with not = true
        $this->mockedBuilder->expects($this->once())
            ->method('whereNull')
            ->with($field, $boolean, true);

        $condition = new NullCondition($field, $boolean, true);
        $condition->apply($this->mockedBuilder);
    }

    public function test_constructor_throws_exception_for_null_field(): void
    {
        $this->expectException(\TypeError::class);
        new NullCondition(null);
    }

    public function test_apply_handles_empty_field_name(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('whereNull')
            ->with('');

        $condition = new NullCondition('');
        $condition->apply($this->mockedBuilder);
    }
} 