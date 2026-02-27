<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Bus;

use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Bus\BusRegOtherService as Entity;

/**
 * BusRegOtherService Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class BusRegOtherServiceEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $busReg = new BusReg();
        $serviceNo = 'foo';
        $busRegOtherService = new Entity($busReg, $serviceNo);
        $this->assertEquals($busReg, $busRegOtherService->getBusReg());
        $this->assertEquals($serviceNo, $busRegOtherService->getServiceNo());
    }

    public function testGetCalculatedValues(): void
    {
        $busRegOtherService = new Entity(new BusReg(), 'foo');
        $this->assertEquals($busRegOtherService->getCalculatedValues(), ['busReg' => null]);
    }
}
