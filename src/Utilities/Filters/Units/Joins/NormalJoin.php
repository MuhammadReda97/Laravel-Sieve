<?php

namespace SortifyLoom\Utilities\Filters\Units\Joins;

use SortifyLoom\Utilities\Filters\Units\Conditions\BaseCondition;

class NormalJoin extends Join
{
    /**
     * @var BaseCondition[]
     */
    private array $conditions = [];
    public readonly string $operation;

    public function __construct(string $table, public readonly string $first, public readonly string $second, string $type = 'inner', string $name = null)
    {
        parent::__construct($table, $type, $name);
        $this->operation = '=';
    }

    public function appendCondition(BaseCondition $condition): self
    {
        $this->conditions[] = $condition;
        return $this;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }
}
