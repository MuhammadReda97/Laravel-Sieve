<?php

namespace RedaLabs\LaravelFilters\Utilities\Enums\Operators;

enum OperatorEnum: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '!=';
    case DB_NOT_EQUALS = '<>';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_THAN_OR_EQUALS = '>=';
    case LESS_THAN_OR_EQUALS = '<=';
    case LIKE = 'like';
    case NOT_LIKE = 'not like';

    public static function isValid(string $operator): bool
    {
        return !is_null(self::tryFrom($operator));
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
} 