<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitJurisdictionQuota as Entity;
use Mockery as m;

/**
 * IrhpPermitJurisdictionQuota Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpPermitJurisdictionQuotaEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreate(): void
    {
        $trafficArea = m::mock(TrafficArea::class);
        $stock = m::mock(IrhpPermitStock::class);

        $entity = $this->createEntity($trafficArea, $stock);

        self::assertInstanceOf(Entity::class, $entity);
        self::assertEquals($trafficArea, $entity->getTrafficArea());
        self::assertEquals($stock, $entity->getIrhpPermitStock());
    }

    public function testUpdate(): void
    {
        $quotaNumber = 999;

        $entity = $this->createEntity();
        $entity->update($quotaNumber);

        $this->assertEquals($quotaNumber, $entity->getQuotaNumber());
    }

    /**
     * Create an entity, optionally passing in customised traffic area and stock
     */
    private function createEntity(mixed $trafficArea = null, mixed $stock = null): mixed
    {
        if ($trafficArea === null) {
            $trafficArea = m::mock(TrafficArea::class);
        }

        if ($stock === null) {
            $stock = m::mock(IrhpPermitStock::class);
        }

        return Entity::create($trafficArea, $stock);
    }
}
