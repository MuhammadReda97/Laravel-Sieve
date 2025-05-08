<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Conditions\LogicalOperatorEnum;
use ArchiTools\LaravelSieve\Enums\Operators\OperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Conditions\InvalidLogicalOperatorException;
use ArchiTools\LaravelSieve\Exceptions\Operators\InvalidOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\JsonLengthCondition;
use Tests\TestCase;

class JsonLengthConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $field = 'tags';
        $operator = '>';
        $value = 2;
        $boolean = 'and';

        $condition = new JsonLengthCondition($field, $operator, $value, $boolean);

        $this->assertEquals($field, $condition->field);
        $this->assertEquals($operator, $condition->operator);
        $this->assertEquals($value, $condition->value);
        $this->assertEquals($boolean, $condition->boolean);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new JsonLengthCondition('tags', '>', 2);

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_not_accept_invalid_operator(): void
    {
        $this->expectException(InvalidOperatorException::class);
        new JsonLengthCondition('tags', 'INVALID', 2);
    }

    public function test_condition_not_accept_invalid_logical_operator(): void
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new JsonLengthCondition('tags', '>', 2, 'INVALID');
    }

    public function test_condition_accepts_only_valid_operators(): void
    {
        $validOperators = OperatorEnum::values();
        $field = 'tags';
        $value = 2;

        foreach ($validOperators as $operator) {
            try {
                new JsonLengthCondition($field, $operator, $value);
                $this->addToAssertionCount(1);
            } catch (InvalidOperatorException $e) {
                $this->fail("Operator {$operator} should be valid but threw exception");
            }
        }
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $field = 'tags';
        $value = 2;
        $operator = '>';

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new JsonLengthCondition($field, $operator, $value, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$operator} should be valid but threw exception");
            }
        }
    }

    public function test_apply_adds_where_json_length_clause_to_builder(): void
    {
        $field = 'tags';
        $operator = '>';
        $value = 2;
        $boolean = 'and';

        $condition = new JsonLengthCondition($field, $operator, $value, $boolean);

        $this->mockedBuilder->expects($this->once())
            ->method('whereJsonLength')
            ->with($field, $operator, $value, $boolean);

        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $field = 'tags';
        $operator = '>';
        $value = 2;

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereJsonLength')
                ->with($field, $operator, $value, $boolean);

            $condition = new JsonLengthCondition($field, $operator, $value, $boolean);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_apply_works_with_different_value_types(): void
    {
        $field = 'tags';
        $operator = '>';
        $testCases = [
            'integer value' => 2,
            'string value' => '2',
            'float value' => 2.0,
        ];

        foreach ($testCases as $description => $value) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereJsonLength')
                ->with($field, $operator, $value);

            $condition = new JsonLengthCondition($field, $operator, $value);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_field(): void
    {
        $this->expectException(\TypeError::class);
        new JsonLengthCondition(null, '>', 2);
    }

    public function test_apply_handles_empty_field_name(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('whereJsonLength')
            ->with('', '>', 2);

        $condition = new JsonLengthCondition('', '>', 2);
        $condition->apply($this->mockedBuilder);
    }
} 