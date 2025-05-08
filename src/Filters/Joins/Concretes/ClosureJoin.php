<?php

namespace ArchiTools\LaravelSieve\Filters\Joins\Concretes;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Joins\JoinTypeEnum;
use ArchiTools\LaravelSieve\Filters\Joins\Contracts\BaseJoin;

class ClosureJoin extends BaseJoin
{
    /**
     * @param string $table
     * @param Closure $closure
     * @param string $type
     * @param string|null $name
     */
    public function __construct(string $table, public readonly Closure $closure, string $type = JoinTypeEnum::INNER->value, ?string $name = null)
    {
        parent::__construct($table, $type, $name);
    }

    public function apply(Builder $builder): void
    {
        $builder->join($this->table, $this->closure, type: $this->type);
    }
}
