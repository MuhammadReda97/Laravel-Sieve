<?php

namespace RedaLabs\LaravelFilters\Utilities\Filters\Units\Conditions;

use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Utilities\Enums\Conditions\GroupConditionTypeEnum;
use RedaLabs\LaravelFilters\Utilities\Exceptions\Conditions\InvalidGroupConditionException;

class GroupConditions extends BaseCondition
{
    public readonly string $type;

    /**
     * @param array $conditions
     * @throws \Exception
     */
    public function __construct(public readonly array $conditions, string $boolean = 'and')
    {
        $this->validateConditions($this->conditions);
        parent::__construct($boolean);
    }

    /**
     * @param array $conditions
     * @return void
     * @throws \Exception
     */
    private function validateConditions(array $conditions): void
    {
        // todo should be two separate methods , one to validate the conditions in right values , second to validate the group conditions type.
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

    public function apply(Builder $builder): void
    {
        $method = $this->type == GroupConditionTypeEnum::AGGREGATION->value ? 'having' : 'where';
        $builder->$method(function ($query) {
            foreach ($this->conditions as $condition) {
                $condition->apply($query);
            }
        }, boolean: $this->boolean);
    }
}