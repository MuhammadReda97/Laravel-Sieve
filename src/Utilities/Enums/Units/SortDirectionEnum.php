<?php

namespace SortifyLoom\Utilities\Enums\Units;

enum SortDirectionEnum: string
{
    case DESC = 'desc';
    case ASC = 'ASC';

    public static function default(): string
    {
        return self::DESC->value;
    }
}
