<?php

namespace ArchiTools\LaravelSieve\Enums\Conditions;

enum LogicalOperatorEnum: string
{
    case AND = 'and';
    case OR = 'or';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

