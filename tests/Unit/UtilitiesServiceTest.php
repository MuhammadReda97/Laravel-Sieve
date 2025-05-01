<?php

namespace Test\Unit;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use RedaLabs\LaravelFilters\Criteria;
use RedaLabs\LaravelFilters\Enums\Sorts\SortDirectionEnum;
use RedaLabs\LaravelFilters\Sorts\Contracts\BaseSort;
use Tests\Unit\Core\ConcreteUtilitiesService;

class UtilitiesServiceTest extends TestCase
{
    private Criteria $criteria;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criteria = new Criteria;
    }

    public function testGetCriteriaReturnsInjectedInstance()
    {
        $request = new Request();
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $this->assertSame($this->criteria, $service->getCriteria());
    }

    public function testFreshReturnsNewCriteriaInstance()
    {
        $request = new Request(['name' => 'John']);
        $service = new ConcreteUtilitiesService($this->criteria, $request);
        $service->applyFilters();

        $this->assertNotSame($this->criteria, $service->fresh()->getCriteria());
    }

    public function testApplyFiltersWithStringMethodFilter()
    {
        $request = new Request(['age' => 18]);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applyFilters();

        // Verify the condition was added to criteria
        $this->assertCount(1, $this->getPrivateProperty($this->criteria, 'conditions'));
    }

    public function testApplyFiltersWithFilterInstance()
    {
        $request = new Request(['name' => 'Jon Doe']);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applyFilters();

        $this->assertCount(1, $this->getPrivateProperty($this->criteria, 'conditions'));
    }

    public function testApplyFiltersSkipsEmptyValues()
    {
        $request = new Request(['name' => '']);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applyFilters();

        $this->assertCount(0, $this->getPrivateProperty($this->criteria, 'conditions'));
    }

    public function testApplyFiltersSkipsInvalidFilters()
    {
        $request = new Request([
            'invalid_filter' => 'invalid_value',
        ]);
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applyFilters();

        $conditions = $this->getPrivateProperty($this->criteria, 'conditions');
        $this->assertCount(0, $conditions);
    }


    public function testApplySortsWithValidParameters()
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

    public function testApplySortsWithCustomSortMethod()
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

    public function testApplySortsWithDefaultDirection()
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

    public function testApplySortsWithInvalidDirectionFallsBackToDefault()
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

    public function testApplySortsSkipsInvalidSortFields()
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

    public function testApplySortsDoesNothingWhenNoSortsInRequest()
    {
        $request = new Request();
        $service = new ConcreteUtilitiesService($this->criteria, $request);

        $service->applySorts();

        $this->assertCount(0, $this->getPrivateProperty($this->criteria, 'sorts'));
    }

    public function testSortsRespectConfiguredDefaultDirectionInService()
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

    private function getPrivateProperty(object $object, string $property)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    private function setProperty(object $object, string $property, mixed $value)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
