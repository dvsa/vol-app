<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Mockery as m;

/**
 * IrhpPermitWindow Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class IrhpPermitWindowEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreateUpdate(): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class)->makePartial();
        $startDate = '2019-10-01';
        $endDate = '2019-10-20';

        $updatedStartDate = '2019-11-01';
        $updatedEndDate = '2019-11-20';

        $entity = Entity::create($irhpPermitStock, $startDate, $endDate);

        $this->assertEquals($irhpPermitStock, $entity->getIrhpPermitStock());
        $this->assertSame($startDate, $entity->getStartDate()->format('Y-m-d'));
        $this->assertSame($endDate, $entity->getEndDate()->format('Y-m-d'));

        $entity->update($irhpPermitStock, $updatedStartDate, $updatedEndDate);

        $this->assertEquals($irhpPermitStock, $entity->getIrhpPermitStock());
        $this->assertSame($updatedStartDate, $entity->getStartDate()->format('Y-m-d'));
        $this->assertSame($updatedEndDate, $entity->getEndDate()->format('Y-m-d'));
    }
}
