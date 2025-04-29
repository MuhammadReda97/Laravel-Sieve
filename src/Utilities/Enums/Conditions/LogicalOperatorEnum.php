<?php

namespace RedaLabs\LaravelFilters\Utilities\Enums\Conditions;

enum LogicalOperatorEnum: string
{
    case AND = 'and';
    case OR = 'or';

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }
}

