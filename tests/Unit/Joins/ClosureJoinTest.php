<?php

namespace Unit\Joins;

use Closure;
use RedaLabs\LaravelFilters\Enums\Joins\JoinTypeEnum;
use RedaLabs\LaravelFilters\Exceptions\Joins\InvalidJoinTypeException;
use RedaLabs\LaravelFilters\Filters\Joins\Concretes\ClosureJoin;
use Tests\TestCase;
use TypeError;

class ClosureJoinTest extends TestCase
{
    private Closure $joinClosure;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder->from('users');
        $this->joinClosure = function ($join) {
            $join->on('users.id', '=', 'posts.user_id');
        };
    }

    public function test_join_accept_name()
    {
        $table = 'posts';
        $joinName = 'users_join';
        $join = new ClosureJoin($table, $this->joinClosure, name: $joinName);
        $this->assertEquals($joinName, $join->name);
    }

    public function test_join_miss_pass_name_fallback_to_default()
    {
        $table = 'posts';
        $join = new ClosureJoin($table, $this->joinClosure);
        $this->assertEquals($table, $join->name);
    }


    public function test_join_type_default_value()
    {
        $join = new ClosureJoin('posts', $this->joinClosure);
        $this->assertEquals(JoinTypeEnum::INNER->value, $join->type);
    }

    public function test_join_accept_only_valid_types()
    {
        $validTypes = JoinTypeEnum::values();
        try {
            foreach ($validTypes as $type) {
                new ClosureJoin('posts', $this->joinClosure, type: $type);
            }
            $this->assertTrue(true);
        } catch (InvalidJoinTypeException $exception) {
            $this->fail('Unexpected InvalidJoinTypeException was thrown: ' . $exception->getMessage());
        }
    }

    public function test_join_not_accept_invalid_type()
    {
        $this->expectException(InvalidJoinTypeException::class);
        new ClosureJoin('posts', $this->joinClosure, type: 'INVALID');
    }

    public function test_join_not_accept_invalid_value_for_closure()
    {
        $this->expectException(TypeError::class);
        new ClosureJoin('posts', 'invalid_value');
    }

    public function test_join_apply_respects_join_type()
    {
        $cases = [
            'INNER' => JoinTypeEnum::INNER->value,
            'LEFT' => JoinTypeEnum::LEFT->value,
            'RIGHT' => JoinTypeEnum::RIGHT->value,
        ];

        foreach ($cases as $typeName => $typeValue) {
            $join = new ClosureJoin('posts', $this->joinClosure, type: $typeValue);
            $join->apply($this->builder);

            $sql = $this->builder->toSql();
            $expected = sprintf('%s join "posts" on "users"."id" = "posts"."user_id"', $typeName);
            $this->assertStringContainsString($expected, $sql, "Failed asserting join type: $typeName");
        }
    }

    public function test_join_apply_with_conditions()
    {
        $join = new ClosureJoin('posts', function ($query) {
            $query->on('users.id', '=', 'posts.user_id')
                ->where('posts.status', '=', 'published');
        });
        $join->apply($this->builder);
        $this->assertStringContainsString('INNER join "posts" on "users"."id" = "posts"."user_id" and "posts"."status" = ?', $this->builder->toSql());
        $this->assertContains("published", $this->builder->getBindings());
    }
}