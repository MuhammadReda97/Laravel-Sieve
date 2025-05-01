<?php

namespace Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use RedaLabs\LaravelFilters\Enums\Operators\OperatorEnum;
use RedaLabs\LaravelFilters\Exceptions\Operators\InvalidOperatorException;

class InvalidOperatorExceptionTest extends TestCase
{
    /** @test */
    public function it_creates_exception_with_correct_message(): void
    {
        // Arrange
        $invalidOperator = 'invalid';
        $expectedMessage = "Invalid operator: '{$invalidOperator}'. Allowed operators are: " . implode(', ', OperatorEnum::getValues());

        // Act
        $exception = new InvalidOperatorException($invalidOperator);

        // Assert
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    /** @test */
    public function it_includes_all_valid_operators_in_message(): void
    {
        // Arrange
        $invalidOperator = 'invalid';
        $validOperators = OperatorEnum::getValues();

        // Act
        $exception = new InvalidOperatorException($invalidOperator);

        // Assert
        foreach ($validOperators as $operator) {
            $this->assertStringContainsString($operator, $exception->getMessage());
        }
    }

    /** @test */
    public function it_handles_empty_operator(): void
    {
        // Arrange
        $invalidOperator = '';
        $expectedMessage = "Invalid operator: '{$invalidOperator}'. Allowed operators are: " . implode(', ', OperatorEnum::getValues());

        // Act
        $exception = new InvalidOperatorException($invalidOperator);

        // Assert
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    /** @test */
    public function it_extends_base_exception(): void
    {
        // Act
        $exception = new InvalidOperatorException('invalid');

        // Assert
        $this->assertInstanceOf(\Exception::class, $exception);
    }
} 