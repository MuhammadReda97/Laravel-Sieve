<?php

namespace Tests\Unit\Sorts;

use ArchiTools\LaravelSieve\Sorts\Concretes\RawSort;
use Tests\TestCase;

class RawSortTest extends TestCase
{
    /**
     * @test
     */
    public function test_constructor_initializes_properties(): void
    {
        $expression = 'LENGTH(name)';
        $direction = 'ASC';
        $bindings = [];
        $sort = new RawSort($expression, $direction, $bindings);

        $this->assertEquals($expression, $sort->getExpression());
        $this->assertEquals($direction, $sort->getDirection());
        $this->assertEquals($bindings, $sort->getBindings());
    }

    /**
     * @test
     */
    public function test_constructor_initializes_properties_with_bindings(): void
    {
        $expression = 'FIELD(status, ?, ?, ?)';
        $direction = 'ASC';
        $bindings = ['active', 'pending', 'inactive'];
        $sort = new RawSort($expression, $direction, $bindings);

        $this->assertEquals($expression, $sort->getExpression());
        $this->assertEquals($direction, $sort->getDirection());
        $this->assertEquals($bindings, $sort->getBindings());
    }

    /**
     * @test
     */
    public function test_apply_calls_orderByRaw_with_expression_and_direction(): void
    {
        $expression = 'LENGTH(name)';
        $direction = 'ASC';
        $bindings = [];
        $sort = new RawSort($expression, $direction, $bindings);

        $this->mockedBuilder->expects($this->once())
            ->method('orderByRaw')
            ->with($expression . ' ' . $direction, $bindings);

        $sort->apply($this->mockedBuilder);
    }

    /**
     * @test
     */
    public function test_apply_calls_orderByRaw_with_complex_expression(): void
    {
        $expression = '(views_count * 0.7) + (likes_count * 0.3)';
        $direction = 'DESC';
        $bindings = [];
        $sort = new RawSort($expression, $direction, $bindings);

        $this->mockedBuilder->expects($this->once())
            ->method('orderByRaw')
            ->with($expression . ' ' . $direction, $bindings);

        $sort->apply($this->mockedBuilder);
    }

    /**
     * @test
     */
    public function test_apply_calls_orderByRaw_with_field_expression(): void
    {
        $expression = 'FIELD(status, ?, ?)';
        $direction = 'ASC';
        $bindings = ['active', 'pending'];
        $sort = new RawSort($expression, $direction, $bindings);

        $this->mockedBuilder->expects($this->once())
            ->method('orderByRaw')
            ->with($expression . ' ' . $direction, $bindings);

        $sort->apply($this->mockedBuilder);
    }

    /**
     * @test
     */
    public function test_apply_calls_orderByRaw_with_multiple_expressions(): void
    {
        $expression = 'LENGTH(name) DESC, FIELD(status, ?, ?) ASC';
        $direction = 'ASC';
        $bindings = ['active', 'pending'];
        $sort = new RawSort($expression, $direction, $bindings);

        $this->mockedBuilder->expects($this->once())
            ->method('orderByRaw')
            ->with($expression . ' ' . $direction, $bindings);

        $sort->apply($this->mockedBuilder);
    }

    /**
     * @test
     */
    public function test_apply_calls_orderByRaw_with_custom_expression(): void
    {
        $expression = 'CASE WHEN status = ? THEN 1 WHEN status = ? THEN 2 ELSE 3 END';
        $direction = 'ASC';
        $bindings = ['active', 'pending'];
        $sort = new RawSort($expression, $direction, $bindings);

        $this->mockedBuilder->expects($this->once())
            ->method('orderByRaw')
            ->with($expression . ' ' . $direction, $bindings);

        $sort->apply($this->mockedBuilder);
    }

    /**
     * @test
     */
    public function test_apply_calls_orderByRaw_with_multiple_sorts(): void
    {
        $this->mockedBuilder->expects($this->exactly(2))
            ->method('orderByRaw')
            ->willReturnCallback(function ($expression, $bindings) {
                static $calls = 0;
                if ($calls === 0) {
                    $this->assertEquals('LENGTH(name) ASC', $expression);
                    $this->assertEquals([], $bindings);
                } else {
                    $this->assertEquals('FIELD(status, ?, ?) ASC', $expression);
                    $this->assertEquals(['active', 'pending'], $bindings);
                }
                $calls++;
                return $this->mockedBuilder;
            });

        $sorts = [
            new RawSort('LENGTH(name)', 'ASC', []),
            new RawSort('FIELD(status, ?, ?)', 'ASC', ['active', 'pending'])
        ];

        foreach ($sorts as $sort) {
            $sort->apply($this->mockedBuilder);
        }
    }
}