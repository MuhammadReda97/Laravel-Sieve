<?php

namespace RedaLabs\LaravelFilters\Enums\Operators;

enum OperatorEnum: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '!=';
    case DB_NOT_EQUALS = '<>';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_THAN_OR_EQUALS = '>=';
    case LESS_THAN_OR_EQUALS = '<=';
    case LIKE = 'LIKE';
    case NOT_LIKE = 'NOT LIKE';

    public static function isValid(string $operator): bool
    {
        return !is_null(self::tryFrom(strtoupper(trim($operator))));
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
} 