<?php

namespace Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use ArchiTools\LaravelSieve\Exceptions\Sorts\InvalidSortDirectionException;

class InvalidSortDirectionExceptionTest extends TestCase
{
    public function test_constructor_sets_correct_message(): void
    {
        $direction = 'invalid';
        $exception = new InvalidSortDirectionException($direction);

        $this->assertEquals("Invalid sort direction: $direction", $exception->getMessage());
    }

    public function test_constructor_handles_empty_direction(): void
    {
        $direction = '';
        $exception = new InvalidSortDirectionException($direction);

        $this->assertEquals("Invalid sort direction: $direction", $exception->getMessage());
    }

    public function test_constructor_handles_special_characters(): void
    {
        $direction = 'asc-desc';
        $exception = new InvalidSortDirectionException($direction);

        $this->assertEquals("Invalid sort direction: $direction", $exception->getMessage());
    }

    public function test_exception_is_instance_of_runtime_exception(): void
    {
        $exception = new InvalidSortDirectionException('invalid');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
} 