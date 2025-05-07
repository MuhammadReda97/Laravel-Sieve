<?php

namespace Tests\Unit\Sorts;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use RedaLabs\LaravelFilters\Sorts\Concretes\RawSort;
use Tests\TestCase;

class RawSortTest extends TestCase
{
    public function test_constructor_initializes_properties(): void
    {
        $expression = 'LENGTH(name)';
        $bindings = [];

        $sort = new RawSort($expression, $bindings);

        $this->assertEquals($expression, $sort->expression);
        $this->assertEquals($bindings, $sort->bindings);
    }

    public function test_constructor_initializes_properties_with_bindings(): void
    {
        $expression = 'FIELD(status, ?, ?, ?)';
        $bindings = ['active', 'pending', 'inactive'];

        $sort = new RawSort($expression, $bindings);

        $this->assertEquals($expression, $sort->expression);
        $this->assertEquals($bindings, $sort->bindings);
    }

    public function test_apply_calls_orderByRaw_on_builder(): void
    {
        $expression = 'LENGTH(name)';
        $bindings = [];
        $sort = new RawSort($expression, $bindings);

        /** @var Builder&MockObject $builder */
        $builder = $this->mockedBuilder;
        $builder->expects($this->once())
            ->method('orderByRaw')
            ->with($expression, $bindings);

        $sort->apply($builder);
    }

    public function test_apply_works_with_bindings(): void
    {
        $expression = 'FIELD(status, ?, ?, ?)';
        $bindings = ['active', 'pending', 'inactive'];
        $sort = new RawSort($expression, $bindings);

        /** @var Builder&MockObject $builder */
        $builder = $this->mockedBuilder;
        $builder->expects($this->once())
            ->method('orderByRaw')
            ->with($expression, $bindings);

        $sort->apply($builder);
    }

    public function test_apply_works_with_complex_expressions(): void
    {
        $expression = 'CASE WHEN status = ? THEN 1 WHEN status = ? THEN 2 ELSE 3 END';
        $bindings = ['active', 'pending'];
        $sort = new RawSort($expression, $bindings);

        /** @var Builder&MockObject $builder */
        $builder = $this->mockedBuilder;
        $builder->expects($this->once())
            ->method('orderByRaw')
            ->with($expression, $bindings);

        $sort->apply($builder);
    }

    public function test_apply_works_with_multiple_sorts(): void
    {
        $sorts = [
            new RawSort('LENGTH(name)', []),
            new RawSort('FIELD(status, ?, ?)', ['active', 'pending'])
        ];

        /** @var Builder&MockObject $builder */
        $builder = $this->mockedBuilder;
        $builder->expects($this->exactly(2))
            ->method('orderByRaw')
            ->willReturnCallback(function ($expression, $bindings) use ($builder) {
                static $calls = 0;
                if ($calls === 0) {
                    $this->assertEquals('LENGTH(name)', $expression);
                    $this->assertEquals([], $bindings);
                } else {
                    $this->assertEquals('FIELD(status, ?, ?)', $expression);
                    $this->assertEquals(['active', 'pending'], $bindings);
                }
                $calls++;
                return $builder;
            });

        foreach ($sorts as $sort) {
            $sort->apply($builder);
        }
    }
} 