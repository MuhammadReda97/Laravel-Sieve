<?php

namespace Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use RedaLabs\LaravelFilters\Enums\Conditions\LogicalOperatorEnum;
use RedaLabs\LaravelFilters\Exceptions\Conditions\InvalidLogicalOperatorException;

class InvalidLogicalOperatorExceptionTest extends TestCase
{
    public function test_it_creates_exception_with_correct_message(): void
    {
        $invalidOperator = 'invalid';
        $expectedMessage = "Invalid logical operator: '{$invalidOperator}'. Allowed operators are: " . implode(', ', LogicalOperatorEnum::values());

        $exception = new InvalidLogicalOperatorException($invalidOperator);

        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function test_it_includes_all_valid_operators_in_message(): void
    {
        $invalidOperator = 'invalid';
        $validOperators = LogicalOperatorEnum::values();

        $exception = new InvalidLogicalOperatorException($invalidOperator);

        foreach ($validOperators as $operator) {
            $this->assertStringContainsString($operator, $exception->getMessage());
        }
    }

    public function test_it_handles_empty_operator(): void
    {
        $invalidOperator = '';
        $expectedMessage = "Invalid logical operator: '{$invalidOperator}'. Allowed operators are: " . implode(', ', LogicalOperatorEnum::values());

        $exception = new InvalidLogicalOperatorException($invalidOperator);

        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function test_it_extends_base_exception(): void
    {
        $exception = new InvalidLogicalOperatorException('invalid');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
} 