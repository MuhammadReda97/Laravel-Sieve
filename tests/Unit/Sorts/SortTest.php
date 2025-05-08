<?php

namespace Tests\Unit\Sorts;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use ArchiTools\LaravelSieve\Enums\Sorts\SortDirectionEnum;
use ArchiTools\LaravelSieve\Exceptions\Sorts\InvalidSortDirectionException;
use ArchiTools\LaravelSieve\Sorts\Concretes\Sort;
use Tests\TestCase;

class SortTest extends TestCase
{
    public function test_constructor_initializes_properties(): void
    {
        $field = 'name';
        $direction = SortDirectionEnum::ASC->value;
        $sort = new Sort($field, $direction);

        $this->assertEquals($field, $sort->getField());
        $this->assertEquals($direction, $sort->getDirection());
    }

    public function test_constructor_throws_exception_for_invalid_direction(): void
    {
        $field = 'name';
        $invalidDirection = 'INVALID';

        $this->expectException(InvalidSortDirectionException::class);
        $this->expectExceptionMessage("Invalid sort direction: $invalidDirection");

        new Sort($field, $invalidDirection);
    }

    public function test_apply_calls_orderBy_on_builder(): void
    {
        $field = 'name';
        $direction = SortDirectionEnum::ASC->value;
        $sort = new Sort($field, $direction);

        /** @var Builder&MockObject $builder */
        $builder = $this->mockedBuilder;
        $builder->expects($this->once())
            ->method('orderBy')
            ->with($field, $direction);

        $sort->apply($builder);
    }

    public function test_apply_works_with_desc_direction(): void
    {
        $field = 'name';
        $direction = SortDirectionEnum::DESC->value;
        $sort = new Sort($field, $direction);

        /** @var Builder&MockObject $builder */
        $builder = $this->mockedBuilder;
        $builder->expects($this->once())
            ->method('orderBy')
            ->with($field, $direction);

        $sort->apply($builder);
    }

    public function test_apply_works_with_table_qualified_field(): void
    {
        $field = 'users.name';
        $direction = SortDirectionEnum::ASC->value;
        $sort = new Sort($field, $direction);

        /** @var Builder&MockObject $builder */
        $builder = $this->mockedBuilder;
        $builder->expects($this->once())
            ->method('orderBy')
            ->with($field, $direction);

        $sort->apply($builder);
    }

    public function test_apply_works_with_multiple_sorts(): void
    {
        $sorts = [
            new Sort('name', SortDirectionEnum::ASC->value),
            new Sort('created_at', SortDirectionEnum::DESC->value)
        ];

        /** @var Builder&MockObject $builder */
        $builder = $this->mockedBuilder;
        $builder->expects($this->exactly(2))
            ->method('orderBy')
            ->willReturnCallback(function ($field, $direction) use ($builder) {
                static $calls = 0;
                if ($calls === 0) {
                    $this->assertEquals('name', $field);
                    $this->assertEquals(SortDirectionEnum::ASC->value, $direction);
                } else {
                    $this->assertEquals('created_at', $field);
                    $this->assertEquals(SortDirectionEnum::DESC->value, $direction);
                }
                $calls++;
                return $builder;
            });

        foreach ($sorts as $sort) {
            $sort->apply($builder);
        }
    }
} 