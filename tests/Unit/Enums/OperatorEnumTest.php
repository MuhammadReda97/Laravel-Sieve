<?php

namespace Tests\Unit\Enums;

use PHPUnit\Framework\TestCase;
use ArchiTools\LaravelSieve\Enums\Operators\OperatorEnum;

class OperatorEnumTest extends TestCase
{
    public function test_it_has_all_required_operators(): void
    {
        $expectedOperators = [
            '=' => 'EQUALS',
            '!=' => 'NOT_EQUALS',
            '<>' => 'DB_NOT_EQUALS',
            '>' => 'GREATER_THAN',
            '<' => 'LESS_THAN',
            '>=' => 'GREATER_THAN_OR_EQUALS',
            '<=' => 'LESS_THAN_OR_EQUALS',
            'LIKE' => 'LIKE',
            'NOT LIKE' => 'NOT_LIKE',
        ];

        foreach ($expectedOperators as $value => $name) {
            $this->assertTrue(OperatorEnum::isValid($value), "Operator {$value} should be valid");
            $this->assertEquals($name, OperatorEnum::from($value)->name);
        }
    }

    public function test_it_validates_operators_correctly(): void
    {
        $this->assertTrue(OperatorEnum::isValid('='));
        $this->assertTrue(OperatorEnum::isValid('!='));
        $this->assertTrue(OperatorEnum::isValid('<>'));
        $this->assertTrue(OperatorEnum::isValid('>'));
        $this->assertTrue(OperatorEnum::isValid('<'));
        $this->assertTrue(OperatorEnum::isValid('>='));
        $this->assertTrue(OperatorEnum::isValid('<='));
        $this->assertTrue(OperatorEnum::isValid('like'));
        $this->assertTrue(OperatorEnum::isValid('not like'));

        $this->assertFalse(OperatorEnum::isValid('invalid'));
        $this->assertFalse(OperatorEnum::isValid(''));
    }

    public function test_it_returns_all_values(): void
    {
        $values = OperatorEnum::values();

        $this->assertIsArray($values);
        $this->assertNotEmpty($values);
        $this->assertContains('=', $values);
        $this->assertContains('!=', $values);
        $this->assertContains('<>', $values);
        $this->assertContains('>', $values);
        $this->assertContains('<', $values);
        $this->assertContains('>=', $values);
        $this->assertContains('<=', $values);
        $this->assertContains('LIKE', $values);
        $this->assertContains('NOT LIKE', $values);
    }

    public function test_it_handles_case_insensitive_validation(): void
    {
        $this->assertTrue(OperatorEnum::isValid('LIKE'));
        $this->assertTrue(OperatorEnum::isValid('Like'));
        $this->assertTrue(OperatorEnum::isValid('like'));
    }

    public function test_it_handles_whitespace_in_validation(): void
    {
        $this->assertTrue(OperatorEnum::isValid(' not like '));
        $this->assertTrue(OperatorEnum::isValid('not like'));
    }
} 