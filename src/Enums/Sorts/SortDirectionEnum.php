<?php

namespace ArchiTools\LaravelSieve\Enums\Sorts;

enum SortDirectionEnum: string
{
    case DESC = 'DESC';
    case ASC = 'ASC';

    public static function default(): string
    {
        return self::ASC->value;
    }

    public static function values(): array
    {
        return array_map(function ($case) {
            return $case->value;
        }, self::cases());
    }
}
