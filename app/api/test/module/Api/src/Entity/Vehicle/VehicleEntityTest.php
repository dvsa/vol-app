<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Vehicle;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * @covers Dvsa\Olcs\Api\Entity\Vehicle\Vehicle
 * @covers Dvsa\Olcs\Api\Entity\Vehicle\AbstractVehicle
 */
final class VehicleEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstuctor(): void
    {
        $sut = new Vehicle();

        $this->assertInstanceOf(ArrayCollection::class, $sut->getLicenceVehicles());
    }
}
