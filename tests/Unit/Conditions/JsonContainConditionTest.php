<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use RedaLabs\LaravelFilters\Enums\Conditions\LogicalOperatorEnum;
use RedaLabs\LaravelFilters\Exceptions\Conditions\InvalidLogicalOperatorException;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\JsonContainCondition;
use Tests\TestCase;

class JsonContainConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $field = 'tags';
        $value = 'php';
        $boolean = 'and';
        $not = false;

        $condition = new JsonContainCondition($field, $value, $boolean, $not);

        $this->assertEquals($field, $condition->field);
        $this->assertEquals($value, $condition->value);
        $this->assertEquals($boolean, $condition->boolean);
        $this->assertEquals($not, $condition->not);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new JsonContainCondition('tags', 'php');

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_uses_default_not_when_not_provided(): void
    {
        $condition = new JsonContainCondition('tags', 'php');

        $this->assertFalse($condition->not);
    }

    public function test_condition_not_accept_invalid_logical_operator(): void
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new JsonContainCondition('tags', 'php', 'INVALID');
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $field = 'tags';
        $value = 'php';

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new JsonContainCondition($field, $value, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$logicalOperator} should be valid but threw exception");
            }
        }
    }

    public function test_apply_adds_where_json_contains_clause_to_builder(): void
    {
        $field = 'tags';
        $value = 'php';
        $boolean = 'and';
        $not = false;

        $condition = new JsonContainCondition($field, $value, $boolean, $not);

        $this->mockedBuilder->expects($this->once())
            ->method('whereJsonContains')
            ->with($field, $value, $boolean, $not);

        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $field = 'tags';
        $value = 'php';

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereJsonContains')
                ->with($field, $value, $boolean, false);

            $condition = new JsonContainCondition($field, $value, $boolean);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_apply_works_with_not_parameter(): void
    {
        $field = 'tags';
        $value = 'php';
        $boolean = 'and';

        // Test with not = true
        $this->mockedBuilder->expects($this->once())
            ->method('whereJsonContains')
            ->with($field, $value, $boolean, true);

        $condition = new JsonContainCondition($field, $value, $boolean, true);
        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_value_types(): void
    {
        $field = 'tags';
        $testCases = [
            'string value' => 'php',
            'integer value' => 1,
            'array value' => ['php', 'laravel'],
            'object value' => (object)['name' => 'php'],
            'null value' => null,
            'boolean value' => true,
        ];

        foreach ($testCases as $description => $value) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereJsonContains')
                ->with($field, $value);

            $condition = new JsonContainCondition($field, $value);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_field(): void
    {
        $this->expectException(\TypeError::class);
        new JsonContainCondition(null, 'php');
    }

    public function test_apply_handles_empty_field_name(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('whereJsonContains')
            ->with('', 'php');

        $condition = new JsonContainCondition('', 'php');
        $condition->apply($this->mockedBuilder);
    }
} 