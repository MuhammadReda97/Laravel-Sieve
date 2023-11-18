<?php

namespace SortifyLoom\Utilities\Sorts\Units;

use SortifyLoom\Utilities\Sorts\Abstractions\Sort;

class RawSort extends Sort
{
    public function __construct(public readonly string $expression)
    {
    }
}
