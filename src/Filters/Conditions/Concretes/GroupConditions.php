<?php

namespace RedaLabs\LaravelFilters\Filters\Conditions\Concretes;

use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use RedaLabs\LaravelFilters\Enums\Conditions\GroupConditionTypeEnum;
use RedaLabs\LaravelFilters\Exceptions\Conditions\EmptyGroupConditionsException;
use RedaLabs\LaravelFilters\Exceptions\Conditions\MixedGroupConditionException;
use RedaLabs\LaravelFilters\Filters\Conditions\Contracts\BaseCondition;

class GroupConditions extends BaseCondition
{
    public readonly string $type;

    /**
     * @param BaseCondition[] $conditions
     * @param string $boolean
     * @throws Exception
     */
    public function __construct(public readonly array $conditions, string $boolean = 'and')
    {
        if (empty($this->conditions)) {
            throw new EmptyGroupConditionsException;
        }
        $this->validateConditions($this->conditions);
        parent::__construct($boolean);
    }

    /**
     * @param array $conditions
     * @return void
     * @throws Exception
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
            throw new MixedGroupConditionException;
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