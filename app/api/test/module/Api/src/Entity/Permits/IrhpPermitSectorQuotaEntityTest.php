<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\Sectors;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota as Entity;
use Mockery as m;

/**
 * IrhpPermitSectorQuota Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpPermitSectorQuotaEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreate(): void
    {
        $sector = m::mock(Sectors::class);
        $stock = m::mock(IrhpPermitStock::class);
        $entity = $this->createEntity($sector, $stock);

        self::assertInstanceOf(Entity::class, $entity);
        self::assertEquals($sector, $entity->getSector());
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
     * Create an entity, optionally passing in customised sector and stock
     */
    private function createEntity(mixed $sector = null, mixed $stock = null): mixed
    {
        if ($sector === null) {
            $sector = m::mock(Sectors::class);
        }

        if ($stock === null) {
            $stock = m::mock(IrhpPermitStock::class);
        }

        return Entity::create($sector, $stock);
    }
}
