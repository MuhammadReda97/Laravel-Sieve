<?php

namespace SortifyLoom\Utilities\Filters\Units\Joins;

use Closure;

class ClosureJoin extends Join
{
    public function __construct(string $table, public readonly Closure $closure, string $type = 'inner', ?string $name = null)
    {
        parent::__construct($table, $type, $name);
    }
}
