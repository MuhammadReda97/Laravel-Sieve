<?php

namespace ArchiTools\LaravelSieve\Enums\Joins;

enum JoinTypeEnum: string
{
    case INNER = 'INNER';
    case LEFT = 'LEFT';
    case RIGHT = 'RIGHT';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $type): bool
    {
        return !is_null(self::tryFrom(strtoupper(trim($type))));
    }
}

