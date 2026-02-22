<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Vehicle;

use Mockery as m;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc as Entity;

/**
 * GoodsDisc Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class GoodsDiscEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $licenceVehicle = m::mock(LicenceVehicle::class);

        $entity = new Entity($licenceVehicle);

        $this->assertSame($licenceVehicle, $entity->getLicenceVehicle());
    }
}
