<?php

namespace Test\Unit;

use Illuminate\Http\Request;
use RedaLabs\LaravelFilters\Criteria;
use RedaLabs\LaravelFilters\Enums\Sorts\SortDirectionEnum;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;
use Tests\TestCase;
use Tests\Unit\Core\ConcreteUtilitiesService;

class UtilitiesServiceTest extends TestCase
{
    private Criteria $criteria;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criteria = new Criteria;
    }

    public function test_get_criteria_returns_injected_instance()
    {
        $request = new Request();
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $this->assertSame($this->criteria, $service->getCriteria());
    }

    public function test_fresh_returns_new_criteria_instance()
    {
        $request = new Request(['name' => 'John']);
        $service = new ConcreteUtilitiesService($this->criteria, $request);
        $service->applyFilters();

        $this->assertNotSame($this->criteria, $service->fresh()->getCriteria());
    }

    public function test_apply_filters_with_string_method_filter()
    {
        $request = new Request(['age' => 18]);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applyFilters();

        $this->assertCount(1, $this->getPrivateProperty($this->criteria, 'conditions'));
    }

    public function test_apply_filters_with_filter_instance()
    {
        $request = new Request(['name' => 'Jon Doe']);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applyFilters();

        $this->assertCount(1, $this->getPrivateProperty($this->criteria, 'conditions'));
    }

    public function test_apply_filters_skips_empty_values()
    {
        $request = new Request(['name' => '']);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applyFilters();

        $this->assertCount(0, $this->getPrivateProperty($this->criteria, 'conditions'));
    }

    public function test_apply_filters_skips_invalid_filters()
    {
        $request = new Request([
            'invalid_filter' => 'invalid_value',
        ]);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applyFilters();

        $conditions = $this->getPrivateProperty($this->criteria, 'conditions');
        $this->assertCount(0, $conditions);
    }

    public function test_apply_sorts_with_valid_parameters()
    {
        $request = new Request([
            'sorts' => [
                ['field' => 'created_at', 'direction' => 'DESC']
            ]
        ]);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applySorts();

        $sorts = $this->getPrivateProperty($this->criteria, 'sorts');
        $this->assertCount(1, $sorts);
        $this->assertInstanceOf(BaseSort::class, current($sorts));
    }

    public function test_apply_sorts_with_custom_sort_method()
    {
        $request = new Request([
            'sorts' => [
                ['field' => 'name', 'direction' => SortDirectionEnum::ASC->value]

            ]
        ]);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applySorts();

        $sorts = $this->getPrivateProperty($this->criteria, 'sorts');
        $this->assertCount(1, $sorts);
    }

    public function test_apply_sorts_with_default_direction()
    {
        $request = new Request([
            'sorts' => [
                ['field' => 'name'] // No direction provided
            ]
        ]);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applySorts();

        $sorts = $this->getPrivateProperty($this->criteria, 'sorts');
        $this->assertEquals(SortDirectionEnum::default(), current($sorts)->direction);
    }

    public function test_apply_sorts_with_invalid_direction_falls_back_to_default()
    {
        $request = new Request([
            'sorts' => [
                ['field' => 'name', 'direction' => 'INVALID']
            ]
        ]);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applySorts();

        $sorts = $this->getPrivateProperty($this->criteria, 'sorts');
        $this->assertEquals(SortDirectionEnum::default(), current($sorts)->direction);
    }

    public function test_apply_sorts_skips_invalid_sort_fields()
    {
        $request = new Request([
            'sorts' => [
                ['field' => 'invalid_field', 'direction' => 'ASC'],
                ['field' => 'name', 'direction' => 'ASC']
            ]
        ]);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applySorts();

        $sorts = $this->getPrivateProperty($this->criteria, 'sorts');
        $this->assertCount(1, $sorts);
    }

    public function test_apply_sorts_does_nothing_when_no_sorts_in_request()
    {
        $request = new Request();
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applySorts();

        $this->assertCount(0, $this->getPrivateProperty($this->criteria, 'sorts'));
    }

    public function test_sorts_respect_configured_default_direction_in_service()
    {
        $request = new Request([
            'sorts' => [
                ['field' => 'name'] // No direction provided
            ]
        ]);
        $service = new ConcreteUtilitiesService($this->criteria, $request)
            ->applySorts();

        $sorts = $this->getPrivateProperty($this->criteria, 'sorts');
        $this->assertEquals(SortDirectionEnum::default(), current($sorts)->direction);

        $this->setProperty($service, 'defaultSortDirection', SortDirectionEnum::ASC->value);
        $service->applySorts();

        $sorts = $this->getPrivateProperty($this->criteria, 'sorts');
        $this->assertEquals(SortDirectionEnum::ASC->value, current($sorts)->direction);
    }

    private function setProperty(object $object, string $property, mixed $value)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
