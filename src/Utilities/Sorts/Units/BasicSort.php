<?php

namespace SortifyLoom\Utilities\Sorts\Units;

use SortifyLoom\Utilities\Sorts\Abstractions\Sort;

class BasicSort extends Sort
{
    public function __construct(public readonly string $field, public readonly string $direction)
    {
    }
}
