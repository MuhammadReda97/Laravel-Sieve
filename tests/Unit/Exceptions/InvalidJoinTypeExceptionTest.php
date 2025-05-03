<?php

namespace Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use RedaLabs\LaravelFilters\Enums\Joins\JoinTypeEnum;
use RedaLabs\LaravelFilters\Exceptions\Joins\InvalidJoinTypeException;

class InvalidJoinTypeExceptionTest extends TestCase
{

    public function test_test_it_creates_exception_with_correct_message(): void
    {
        $invalidType = 'invalid';
        $expectedMessage = "Invalid join type: '{$invalidType}'. Valid types are: " . implode(', ', JoinTypeEnum::values());

        $exception = new InvalidJoinTypeException($invalidType);

        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function test_it_includes_all_valid_types_in_message(): void
    {
        $invalidType = 'invalid';
        $validTypes = JoinTypeEnum::values();
        $exception = new InvalidJoinTypeException($invalidType);

        foreach ($validTypes as $type) {
            $this->assertStringContainsString($type, $exception->getMessage());
        }
    }

    public function test_it_handles_empty_type(): void
    {
        $invalidType = '';
        $expectedMessage = "Invalid join type: '{$invalidType}'. Valid types are: " . implode(', ', JoinTypeEnum::values());
        $exception = new InvalidJoinTypeException($invalidType);

        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function test_it_extends_base_exception(): void
    {
        $exception = new InvalidJoinTypeException('invalid');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}