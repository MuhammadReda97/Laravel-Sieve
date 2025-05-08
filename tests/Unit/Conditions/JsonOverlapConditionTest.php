<?php

namespace Tests\Unit\Conditions;

use Illuminate\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Conditions\LogicalOperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Conditions\InvalidLogicalOperatorException;
use ArchiTools\LaravelSieve\Filters\Conditions\Concretes\JsonOverlapCondition;
use Tests\TestCase;

class JsonOverlapConditionTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $field = 'tags';
        $value = ['php', 'laravel'];
        $boolean = 'and';
        $not = false;

        $condition = new JsonOverlapCondition($field, $value, $boolean, $not);

        $this->assertEquals($field, $condition->field);
        $this->assertEquals($value, $condition->value);
        $this->assertEquals($boolean, $condition->boolean);
        $this->assertEquals($not, $condition->not);
    }

    public function test_condition_uses_default_boolean_when_not_provided(): void
    {
        $condition = new JsonOverlapCondition('tags', ['php', 'laravel']);

        $this->assertEquals('and', $condition->boolean);
    }

    public function test_condition_uses_default_not_when_not_provided(): void
    {
        $condition = new JsonOverlapCondition('tags', ['php', 'laravel']);

        $this->assertFalse($condition->not);
    }

    public function test_condition_not_accept_invalid_logical_operator(): void
    {
        $this->expectException(InvalidLogicalOperatorException::class);
        new JsonOverlapCondition('tags', ['php', 'laravel'], 'INVALID');
    }

    public function test_condition_accepts_only_valid_logical_operators(): void
    {
        $logicalOperators = LogicalOperatorEnum::values();
        $field = 'tags';
        $value = ['php', 'laravel'];

        foreach ($logicalOperators as $logicalOperator) {
            try {
                new JsonOverlapCondition($field, $value, $logicalOperator);
                $this->addToAssertionCount(1);
            } catch (InvalidLogicalOperatorException $exception) {
                $this->fail("Operator {$logicalOperator} should be valid but threw exception");
            }
        }
    }

    public function test_apply_adds_where_json_overlaps_clause_to_builder(): void
    {
        $field = 'tags';
        $value = ['php', 'laravel'];
        $boolean = 'and';
        $not = false;

        $condition = new JsonOverlapCondition($field, $value, $boolean, $not);

        $this->mockedBuilder->expects($this->once())
            ->method('whereJsonOverlaps')
            ->with($field, $value, $boolean, $not);

        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_boolean_conditions(): void
    {
        $field = 'tags';
        $value = ['php', 'laravel'];

        foreach (LogicalOperatorEnum::values() as $boolean) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereJsonOverlaps')
                ->with($field, $value, $boolean, false);

            $condition = new JsonOverlapCondition($field, $value, $boolean);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_apply_works_with_not_parameter(): void
    {
        $field = 'tags';
        $value = ['php', 'laravel'];
        $boolean = 'and';

        // Test with not = true
        $this->mockedBuilder->expects($this->once())
            ->method('whereJsonOverlaps')
            ->with($field, $value, $boolean, true);

        $condition = new JsonOverlapCondition($field, $value, $boolean, true);
        $condition->apply($this->mockedBuilder);
    }

    public function test_apply_works_with_different_value_types(): void
    {
        $field = 'tags';
        $testCases = [
            'string array' => ['php', 'laravel'],
            'integer array' => [1, 2, 3],
            'mixed array' => ['php', 1, true],
            'empty array' => [],
            'object array' => [(object)['name' => 'php'], (object)['name' => 'laravel']],
        ];

        foreach ($testCases as $value) {
            $this->mockedBuilder->expects($this->once())
                ->method('whereJsonOverlaps')
                ->with($field, $value);

            $condition = new JsonOverlapCondition($field, $value);
            $condition->apply($this->mockedBuilder);
            $this->mockedBuilder = $this->createMock(Builder::class);
        }
    }

    public function test_constructor_throws_exception_for_null_field(): void
    {
        $this->expectException(\TypeError::class);
        new JsonOverlapCondition(null, ['php', 'laravel']);
    }

    public function test_apply_handles_empty_field_name(): void
    {
        $this->mockedBuilder->expects($this->once())
            ->method('whereJsonOverlaps')
            ->with('', ['php', 'laravel']);

        $condition = new JsonOverlapCondition('', ['php', 'laravel']);
        $condition->apply($this->mockedBuilder);
    }
} 