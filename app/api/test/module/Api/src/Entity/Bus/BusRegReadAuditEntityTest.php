<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Bus;

use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Bus\BusRegReadAudit as Entity;

/**
 * BusRegReadAudit Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class BusRegReadAuditEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testBusRegRealAudit(): void
    {
        $user = new User('123', 'foo');
        $busReg = new BusReg();
        $busRegRealAudit = new Entity($user, $busReg);
        $this->assertEquals($busRegRealAudit->getUser(), $user);
        $this->assertEquals($busRegRealAudit->getBusReg(), $busReg);
    }
}
