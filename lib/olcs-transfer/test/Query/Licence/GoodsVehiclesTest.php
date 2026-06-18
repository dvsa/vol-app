<?php

namespace Dvsa\OlcsTest\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Query\Licence\GoodsVehicles;
use Dvsa\Olcs\Transfer\Query\QueryContainer;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class GoodsVehiclesTest extends TestCase
{
    public function testGetVehicleIdsReturnsAnArrayProvided()
    {
        // Setup
        $expectedVehicleIds = [1, 2, 3, 4];
        $sut = GoodsVehicles::create(['vehicleIds' => $expectedVehicleIds]);

        // Assert
        $this->assertEquals($expectedVehicleIds, $sut->getVehicleIds());
    }

    public function testGetVehicleIdsReturnsNullProvided()
    {
        // Setup
        $sut = GoodsVehicles::create(['vehicleIds' => null]);

        // Assert
        $this->assertNull($sut->getVehicleIds());
    }

    public function testGetVehicleIdsReturnsNullWhenNoVehicleIdsProvided()
    {
        // Setup
        $sut = GoodsVehicles::create([]);

        // Assert
        $this->assertNull($sut->getVehicleIds());
    }

    /**
     * @return array[]
     */
    public function getValidVehicleIdSets(): array
    {
        return [
            'no vehicle ids' => [],
            'empty array of vehicle ids' => [[]],
            'max number of vehicle ids' => [array_fill(0, 100, 1)],
            'numeric string vehicle id' => [["1"]],
            'associative vehicle id array' => [["foo" => 1]],
        ];
    }

    /**
     * @dataProvider getValidVehicleIdSets
     */
    public function testItAcceptsValidSetsOfVehicleIds(array $vehicleIdsSet = null): void
    {
        // Setup
        $sut = GoodsVehicles::create(['vehicleIds' => $vehicleIdsSet]);
        $queryContainer = $this->newQueryContainer($sut);

        // Execute
        $queryContainer->isValid();

        // Assert
        $this->assertArrayNotHasKey('vehicleIds', $queryContainer->getMessages(), 'Expected query to have no validation messages for the "vehiclesIds" property');
    }

    /**
     * @return array[]
     */
    public function getInvalidVehicleIdSets(): array
    {
        return [
            'too many vehicle ids' => [array_fill(0, 101, 1)],
            'zero vehicle id' => [[0]],
            'negative vehicle id' => [[-1]],
            'decimal vehicle id' => [[1.05]],
            'alpha vehicle id' => [["a"]],
        ];
    }

    /**
     * @dataProvider getInvalidVehicleIdSets
     */
    public function testItDoesNotAcceptInvalidSetsOfVehicleIds(array $vehicleIdsSet = null): void
    {
        // Setup
        $sut = GoodsVehicles::create(['vehicleIds' => $vehicleIdsSet]);
        $queryContainer = $this->newQueryContainer($sut);

        // Execute
        $queryContainer->isValid();

        // Assert
        $this->assertArrayHasKey('vehicleIds', $queryContainer->getMessages(), 'Expected query to have validation messages for the "vehiclesIds" property');
    }

    /**
     * @return QueryContainer
     */
    public function newQueryContainer(QueryInterface $query): QueryContainer
    {
        $serviceManager = m::mock(ServiceManager::class);

        $annotationBuilder = new AnnotationBuilder();

        $annotationBuilder->setFilterManager(new FilterPluginManager($serviceManager));
        $annotationBuilder->setValidatorManager(new ValidatorPluginManager($serviceManager));

        return $annotationBuilder->createQuery($query);
    }
}
