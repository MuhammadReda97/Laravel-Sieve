<?php

namespace Tests\Unit;

use RedaLabs\LaravelFilters\Criteria;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\Condition;
use RedaLabs\LaravelFilters\Filters\Joins\Concretes\Join;
use RedaLabs\LaravelFilters\Sorts\Concretes\Sort;
use Tests\TestCase;

class CriteriaTest extends TestCase
{
    private Criteria $criteria;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criteria = new Criteria();
    }

    public function test_append_join()
    {
        $join = new Join('test_join', 'test_table.column', '=', 'test_join.column');

        $this->criteria->appendJoin($join);

        $joins = $this->getPrivateProperty($this->criteria, 'joins');
        $this->assertArrayHasKey('test_join', $joins);
        $this->assertCount(1, $joins);
    }

    public function test_append_join_with_custom_sort_order()
    {
        $join = new Join('test_join', 'test_table.column', '=', 'test_join.column');
        $customSort = 200;

        $this->criteria->appendJoin($join, $customSort);

        $joins = $this->getPrivateProperty($this->criteria, 'joins');
        $this->assertEquals($customSort, $joins['test_join']['order']);
    }

    public function test_remove_join_if_exists()
    {
        $join = new Join('test_join', 'test_table.column', '=', 'test_join.column');

        $this->criteria->appendJoin($join);
        $this->criteria->removeJoinIfExists('test_join');

        $joins = $this->getPrivateProperty($this->criteria, 'joins');
        $this->assertArrayNotHasKey('test_join', $joins);
        $this->assertCount(0, $joins);
    }

    public function test_remove_non_existent_join()
    {
        $this->criteria->removeJoinIfExists('non_existent_join');
        $joins = $this->getPrivateProperty($this->criteria, 'joins');
        $this->assertCount(0, $joins);
    }

    public function test_append_condition()
    {
        $condition = new Condition('column', '=', 'value');

        $this->criteria->appendCondition($condition);

        $conditions = $this->getPrivateProperty($this->criteria, 'conditions');
        $this->assertCount(1, $conditions);
        $this->assertSame($condition, $conditions[0]);
    }

    public function test_append_sort()
    {
        $sort = new Sort('column_name', 'asc');

        $this->criteria->appendSort($sort);

        $sorts = $this->getPrivateProperty($this->criteria, 'sorts');
        $this->assertCount(1, $sorts);
        $this->assertSame($sort, $sorts['column_name']);
    }

    public function test_apply_on_builder_with_all_components()
    {
        // Create test components
        $join = new Join('users', 'users.id', '=', 'posts.user_id');
        $condition = new Condition('users.id', '=', 1);
        $sort = new Sort('users.name', 'asc');

        // Add components to criteria
        $this->criteria->appendJoin($join);
        $this->criteria->appendCondition($condition);
        $this->criteria->appendSort($sort);

        // Apply criteria to builder
        $result = $this->criteria->applyOnBuilder($this->builder);

        // Assert the same builder instance is returned
        $this->assertSame($this->builder, $result);

        $sql = $this->builder->toSql();
        $bindings = $this->builder->getBindings();
        $this->assertStringContainsString('join "users" on "users"."id" = "posts"."user_id"', strtolower($sql));
        $this->assertStringContainsString('where "users"."id" = ?', strtolower($sql));
        $this->assertContains(1, $bindings);
        $this->assertStringContainsString('order by "users"."name" asc', strtolower($sql));
    }

    public function test_apply_on_builder_with_multiple_joins_orders_correctly()
    {
        $join1 = new Join('users', 'users.id', '=', 'posts.user_id');
        $join2 = new Join('profiles', 'profiles.user_id', '=', 'users.id');

        // Add with explicit sort order
        $this->criteria->appendJoin($join2, 50); // Should be first
        $this->criteria->appendJoin($join1, 100); // Should be second

        $this->criteria->applyOnBuilder($this->builder);
        $sql = $this->builder->toSql();

        // Verify joins are in correct order
        $join2Pos = strpos($sql, 'profiles');
        $join1Pos = strpos($sql, 'users');
        $this->assertLessThan($join1Pos, $join2Pos);
    }

    public function test_apply_on_builder_with_no_components()
    {
        $result = $this->criteria->applyOnBuilder($this->builder);
        $sql = $this->builder
            ->toSql();

        $this->assertSame($this->builder, $result);
        $this->assertEquals('select *', strtolower($sql));
    }

    public function test_apply_joins_orders_by_sort_key()
    {
        $join1 = new Join('join1', 'table1.column', '=', 'join1.column');
        $join2 = new Join('join2', 'table2.column', '=', 'join2.column');

        // Add joins with different sort orders
        $this->criteria->appendJoin($join1, 200);
        $this->criteria->appendJoin($join2, 100);

        $joins = $this->getPrivateProperty($this->criteria, 'joins');
        $this->assertEquals(100, $joins['join2']['order']);
        $this->assertEquals(200, $joins['join1']['order']);
    }

    public function test_apply_conditions()
    {
        $condition1 = new Condition('column1', '=', 'value1');
        $condition2 = new Condition('column2', '=', 'value2');

        $this->criteria->appendCondition($condition1);
        $this->criteria->appendCondition($condition2);

        $conditions = $this->getPrivateProperty($this->criteria, 'conditions');
        $this->assertCount(2, $conditions);
        $this->assertSame($condition1, $conditions[0]);
        $this->assertSame($condition2, $conditions[1]);
    }

    public function test_apply_sorts()
    {
        $sort1 = new Sort('column1', 'asc');
        $sort2 = new Sort('column2', 'desc');

        $this->criteria->appendSort($sort1);
        $this->criteria->appendSort($sort2);

        $sorts = $this->getPrivateProperty($this->criteria, 'sorts');
        $this->assertCount(2, $sorts);
        $this->assertSame($sort1, $sorts['column1']);
        $this->assertSame($sort2, $sorts['column2']);
    }
}