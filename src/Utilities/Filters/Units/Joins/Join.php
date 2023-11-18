<?php

namespace SortifyLoom\Utilities\Filters\Units\Joins;

abstract class Join
{
    public ?string $name;

    public function __construct(public readonly string $table, public readonly string $type, ?string $name = null)
    {
        $this->name = $name ?? $this->table;
    }
}
