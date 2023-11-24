<?php

namespace SortifyLoom\Utilities\Filters\Units\Conditions;

use SortifyLoom\Utilities\Enums\Conditions\GroupConditionTypeEnum;
use SortifyLoom\Utilities\Exceptions\Conditions\InvalidGroupConditionException;

class GroupConditions extends BaseCondition
{
    public readonly string $type;

    /**
     * @param array $conditions
     * @throws \Exception
     */
    public function __construct(public readonly array $conditions)
    {
        $this->validateConditions($this->conditions);
    }

    /**
     * @param array $conditions
     * @return void
     * @throws \Exception
     */
    private function validateConditions(array $conditions): void
    {
        $aggregationConditionsCount = 0;
        $conditionsCount = count($conditions);
        foreach ($conditions as $condition) {
            if ($condition instanceof AggregationCondition) {
                $aggregationConditionsCount++;
            }
        }

        if (!($aggregationConditionsCount == $conditionsCount || $aggregationConditionsCount == 0)) {
            throw new InvalidGroupConditionException;
        }

        $this->type = $aggregationConditionsCount == $conditionsCount ? GroupConditionTypeEnum::AGGREGATION->value : GroupConditionTypeEnum::BASIC->value;
    }
}