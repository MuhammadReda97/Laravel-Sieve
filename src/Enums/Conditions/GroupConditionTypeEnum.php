<?php

namespace RedaLabs\LaravelFilters\Enums\Conditions;

enum GroupConditionTypeEnum: string
{
    case AGGREGATION = 'aggregation';
    case BASIC = 'basic';
}
