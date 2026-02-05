<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\CompaniesHouse;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * CompaniesHouseAlert Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CompaniesHouseAlertEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testAddReason(): void
    {
        $sut = $this->instantiate($this->entityClass);

        $reason = new RefData('foo');

        $sut->addReason($reason);

        $this->assertEquals(1, $sut->getReasons()->count());
        $this->assertEquals('foo', $sut->getReasons()[0]->getReasonType()->getId());
    }
}
