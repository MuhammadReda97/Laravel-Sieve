<?php

namespace Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use ArchiTools\LaravelSieve\Enums\Operators\OperatorEnum;
use ArchiTools\LaravelSieve\Exceptions\Operators\InvalidOperatorException;

class InvalidOperatorExceptionTest extends TestCase
{
    public function test_it_creates_exception_with_correct_message(): void
    {
        $invalidOperator = 'invalid';
        $expectedMessage = "Invalid operator: '{$invalidOperator}'. Allowed operators are: " . implode(', ', OperatorEnum::values());
        $exception = new InvalidOperatorException($invalidOperator);

        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function test_it_includes_all_valid_operators_in_message(): void
    {
        $invalidOperator = 'invalid';
        $validOperators = OperatorEnum::values();
        $exception = new InvalidOperatorException($invalidOperator);

        foreach ($validOperators as $operator) {
            $this->assertStringContainsString($operator, $exception->getMessage());
        }
    }

    public function test_it_handles_empty_operator(): void
    {
        $invalidOperator = '';
        $expectedMessage = "Invalid operator: '{$invalidOperator}'. Allowed operators are: " . implode(', ', OperatorEnum::values());
        $exception = new InvalidOperatorException($invalidOperator);

        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function test_it_extends_base_exception(): void
    {
        $exception = new InvalidOperatorException('invalid');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
} 