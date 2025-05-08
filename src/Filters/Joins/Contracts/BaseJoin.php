<?php

namespace ArchiTools\LaravelSieve\Filters\Joins\Contracts;

use Illuminate\Contracts\Database\Query\Builder;
use ArchiTools\LaravelSieve\Enums\Joins\JoinTypeEnum;
use ArchiTools\LaravelSieve\Exceptions\Joins\InvalidJoinTypeException;

abstract class BaseJoin
{
    public readonly string $name;

    /**
     * @param string $table
     * @param string $type
     * @param string|null $name
     */
    public function __construct(public readonly string $table, public readonly string $type, ?string $name = null)
    {
        if (!JoinTypeEnum::isValid($this->type)) {
            throw new InvalidJoinTypeException($this->type);
        }
        $this->name = $name ?? $this->table;
    }

    abstract public function apply(Builder $builder): void;
}
