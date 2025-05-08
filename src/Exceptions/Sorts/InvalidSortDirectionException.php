<?php

namespace ArchiTools\LaravelSieve\Exceptions\Sorts;

use RuntimeException;

class InvalidSortDirectionException extends RuntimeException
{
    /**
     * @param string $direction
     */
    public function __construct(string $direction)
    {
        parent::__construct("Invalid sort direction: $direction");
    }
}