<?php

namespace Tests\Unit\Conditions;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Conditions\LogicalOperatorEnum;
use ArchiTools\LaravelSieve\Enums\Operators\OperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Conditions\InvalidLogicalOperatorException;
use ArchiTools\LaravelSieve\Exceptions\Operators\InvalidOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\DateCondition;
use Tests\TestCase;
use TypeError;

class DateConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $field = 'created_at';
        $operator = '=';
        $value = '2024-01-01';
        $boolean = 'and';

        $condition = new DateCondition($field, $operator, $value, $boolean);

        $this->assertEquals($field, $condition->field);
        $this->assertEquals($operator, $condition->operator);
        $this->assertEquals($value, $condition->value);
        $this->assertEquals($boolean, $condition->boolean);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new DateCondition('created_at', '=', '2024-01-01');

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_not_accept_invalid_operator(): void
    {
        $this->expectException(InvalidOperatorException::class);
        new DateCondition('created_at', 'INVALID', '2024-01-01');
    }

    public function test_condition_not_accept_invalid_logical_operator(): void
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new DateCondition('created_at', '=', '2024-01-01', 'INVALID');
    }

    public function test_condition_accepts_only_valid_operators(): void
    {
        $validOperators = OperatorEnum::values();
        $field = 'created_at';
        $value = '2024-01-01';

        foreach ($validOperators as $operator) {
            try {
                new DateCondition($field, $operator, $value);
                $this->addToAssertionCount(1);
            } catch (InvalidOperatorException $exception) {
                $this->fail("Operator {$operator} should be valid but threw exception");
            }
        }
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $field = 'created_at';
        $value = '2024-01-01';
        $operator = '=';

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new DateCondition($field, $operator, $value, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$operator} should be valid but threw exception");
            }
        }
    }

    public function test_apply_adds_where_date_clause_to_builder(): void
    {
        $field = 'created_at';
        $operator = '=';
        $value = '2024-01-01';
        $boolean = 'and';

        $condition = new DateCondition($field, $operator, $value, $boolean);

        $this->mockedBuilder->expects($this->once())
            ->method('whereDate')
            ->with($field, $operator, $value, $boolean);

        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $field = 'created_at';
        $operator = '=';
        $value = '2024-01-01';

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereDate')
                ->with($field, $operator, $value, $boolean);

            $condition = new DateCondition($field, $operator, $value, $boolean);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_apply_works_with_different_date_formats(): void
    {
        $field = 'created_at';
        $operator = '=';
        $testCases = [
            'YYYY-MM-DD' => '2024-01-01',
            'YYYY/MM/DD' => '2024/01/01',
            'DD-MM-YYYY' => '01-01-2024',
            'DD/MM/YYYY' => '01/01/2024',
            'timestamp' => '1704067200',
            'Carbon instance' => new Carbon('2024-01-01'),
        ];

        foreach ($testCases as $value) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereDate')
                ->with($field, $operator, $value);

            $condition = new DateCondition($field, $operator, $value);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_field(): void
    {
        $this->expectException(TypeError::class);
        new DateCondition(null, '=', '2024-01-01');
    }

    public function test_apply_handles_empty_field_name(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('whereDate')
            ->with('', '=', '2024-01-01');

        $condition = new DateCondition('', '=', '2024-01-01');
        $condition->apply($this->mockedBuilder);
    }
} 