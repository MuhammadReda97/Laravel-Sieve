<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use RedaLabs\LaravelFilters\Enums\Conditions\LogicalOperatorEnum;
use RedaLabs\LaravelFilters\Enums\Operators\OperatorEnum;
use RedaLabs\LaravelFilters\Exceptions\Conditions\InvalidLogicalOperatorException;
use RedaLabs\LaravelFilters\Exceptions\Operators\InvalidOperatorException;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\Condition;
use Tests\TestCase;
use TypeError;

class ConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $field = 'name';
        $operator = '=';
        $value = 'John';
        $boolean = 'and';

        $condition = new Condition($field, $operator, $value, $boolean);

        $this->assertEquals($field, $condition->field);
        $this->assertEquals($operator, $condition->operator);
        $this->assertEquals($value, $condition->value);
        $this->assertEquals($boolean, $condition->boolean);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new Condition('name', '=', 'John');

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_not_accept_invalid_operator(): void
    {
        $this->expectException(InvalidOperatorException::class);
        new Condition('name', 'INVALID', 'John');
    }

    public function test_condition_not_accept_invalid_logical_operator()
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new Condition('name', '=', 'John', 'INVALID');
    }

    public function test_apply_adds_where_clause_to_builder(): void
    {
        $field = 'name';
        $operator = '=';
        $value = 'John';
        $boolean = 'and';

        $condition = new Condition($field, $operator, $value, $boolean);

        $this->mockedBuilder->expects($this->once())
            ->method('where')
            ->with($field, $operator, $value, $boolean);

        $condition->apply($this->mockedBuilder);
    }

    public function test_condition_accepts_only_valid_operators(): void
    {
        $validOperators = OperatorEnum::values();
        $field = 'name';
        $value = 'John';

        foreach ($validOperators as $operator) {
            try {
                new Condition($field, $operator, $value);
                $this->addToAssertionCount(1);
            } catch (InvalidOperatorException $e) {
                $this->fail("Operator {$operator} should be valid but threw exception");
            }
        }
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $field = 'name';
        $value = 'John';
        $operator = '=';

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new Condition($field, $operator, $value, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$operator} should be valid but threw exception");
            }
        }
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $field = 'name';
        $operator = '=';
        $value = 'John';

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('where')
                ->with($field, $operator, $value, $boolean);

            $condition = new Condition(
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
        $field = 'age';
        $operator = '=';

        $testCases = [
            'integer value' => 25,
            'string value' => '25',
            'null value' => null,
            'boolean value' => true,
            'float value' => 25.5,
        ];

        foreach ($testCases as $description => $value) {
            $this->mockedBuilder->expects($this->once())
                ->method('where')
                ->with($field, $operator, $value);

            $condition = new Condition($field, $operator, $value);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_field(): void
    {
        $this->expectException(TypeError::class);
        new Condition(null, '=', 'value');
    }

    public function test_apply_handles_empty_field_name(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('where')
            ->with('', '=', 'value');

        $condition = new Condition('', '=', 'value');
        $condition->apply($this->mockedBuilder);
    }
}