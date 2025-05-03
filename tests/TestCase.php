<?php

namespace Tests;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $requiresBuilder = true;

    protected Builder $builder;

    protected $mockedBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        if (!$this->requiresBuilder) {
            return;
        }
        $connection = $this->createMock(Connection::class);
        $grammar = new Grammar($connection);
        $processor = new Processor();
        $this->builder = new Builder($connection, $grammar, $processor);
        $this->mockedBuilder = $this->createMock(Builder::class);
    }

    protected function getPrivateProperty(object $object, string $property)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}