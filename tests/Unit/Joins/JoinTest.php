<?php

namespace Unit\Joins;

use Illuminate\Database\Query\Builder;
use RedaLabs\LaravelFilters\Enums\Joins\JoinTypeEnum;
use RedaLabs\LaravelFilters\Enums\Operators\OperatorEnum;
use RedaLabs\LaravelFilters\Exceptions\Joins\InvalidJoinTypeException;
use RedaLabs\LaravelFilters\Exceptions\Operators\InvalidOperatorException;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\Condition;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\InCondition;
use RedaLabs\LaravelFilters\Filters\Joins\Concretes\Join;
use Tests\TestCase;

class JoinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->builder->from('users');
    }

    public function test_join_accept_name()
    {
        $table = 'posts';
        $joinName = 'users_join';
        $join = new Join($table, 'users.id', '=', 'posts.user_id', name: $joinName);
        $this->assertEquals($joinName, $join->name);
    }

    public function test_join_miss_pass_name_fallback_to_default()
    {
        $table = 'posts';
        $join = new Join($table, 'users.id', '=', 'posts.user_id');
        $this->assertEquals($table, $join->name);
    }

    public function test_join_accept_only_valid_operators()
    {
        $validOperators = OperatorEnum::values();
        try {
            foreach ($validOperators as $operator) {
                new Join('posts', 'users.id', $operator, 'posts.user_id');
            }
            $this->assertTrue(true);
        } catch (InvalidOperatorException $exception) {
            $this->fail('Unexpected InvalidOperatorException was thrown: ' . $exception->getMessage());
        }
    }

    public function test_join_not_accept_invalid_operators()
    {
        $this->expectException(InvalidOperatorException::class);
        new Join('posts', 'users.id', 'INVALID', 'posts.user_id');
    }

    public function test_join_accept_conditions()
    {
        $join = new Join('posts', 'users.id', '=', 'posts.user_id');
        $join->appendCondition(new Condition('users.id', '=', 'posts.user_id'));
        $joinConditions = $this->getPrivateProperty($join, 'conditions');
        $this->assertCount(1, $joinConditions);
        $join->appendCondition(new InCondition('users.id', [1, 2, 3], not: true));
        $joinConditions = $this->getPrivateProperty($join, 'conditions');
        $this->assertCount(2, $joinConditions);
    }

    public function test_join_type_default_value()
    {
        $join = new Join('posts', 'users.id', '=', 'posts.user_id');
        $this->assertEquals(JoinTypeEnum::INNER->value, $join->type);
    }

    public function test_join_accept_only_valid_types()
    {
        try {
            foreach (JoinTypeEnum::values() as $type) {
                new Join('posts', 'users.id', '=', 'posts.user_id', type: $type);
            }
            $this->assertTrue(true);
        } catch (InvalidJoinTypeException $exception) {
            $this->fail('Unexpected InvalidJoinTypeException was thrown: ' . $exception->getMessage());
        }
    }

    public function test_join_not_accept_valid_type()
    {
        $this->expectException(InvalidJoinTypeException::class);
        new Join('posts', 'users.id', '=', 'posts.user_id', type: 'INVALID');
    }

    public function test_join_apply_respects_join_type()
    {
        $cases = [
            'INNER' => JoinTypeEnum::INNER->value,
            'LEFT' => JoinTypeEnum::LEFT->value,
            'RIGHT' => JoinTypeEnum::RIGHT->value,
        ];

        foreach ($cases as $typeName => $typeValue) {
            $join = new Join('posts', 'users.id', '=', 'posts.user_id', type: $typeValue);
            $this->mockedBuilder = $this->getMockBuilder(Builder::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['join'])
                ->getMock();

            $this->mockedBuilder->expects($this->once())
                ->method('join')
                ->with(
                    'posts',
                    $this->isInstanceOf(\Closure::class)
                );

            $join->apply($this->mockedBuilder);
            $join->apply($this->builder);

            $sql = $this->builder->toSql();
            $expected = sprintf('%s join "posts" on "users"."id" = "posts"."user_id"', $typeName);
            $this->assertStringContainsString($expected, $sql, "Failed asserting join type: $typeName");
        }
    }

    public function test_join_apply_with_conditions()
    {
        $join = new Join('posts', 'users.id', '=', 'posts.user_id');
        $join->appendCondition(new Condition('posts.status', '=', 'published'));
        $join->apply($this->builder);
        $this->assertStringContainsString('INNER join "posts" on "users"."id" = "posts"."user_id" and "posts"."status" = ?', $this->builder->toSql());
        $this->assertContains("published", $this->builder->getBindings());
    }
}
