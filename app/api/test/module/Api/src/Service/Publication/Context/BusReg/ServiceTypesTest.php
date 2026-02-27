<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\BusReg;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusServiceType;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\BusReg\ServiceTypes;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ServiceTypesTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ServiceTypesTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the bus reg service designation filter')]
    public function testProvide(): void
    {
        $description = 'description';
        $description2 = 'description 2';

        $serviceType = new BusServiceType();
        $serviceType->setDescription($description);

        $serviceType2 = new BusServiceType();
        $serviceType2->setDescription($description2);

        $serviceTypes = new ArrayCollection([$serviceType, $serviceType2]);

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getBusServiceTypes')->andReturn($serviceTypes);

        $publication = m::mock(PublicationLink::class);
        $publication->shouldReceive('getBusReg')->andReturn($busReg);

        $sut = new ServiceTypes(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $output = [
            'busServiceTypes' => $description . ' / ' . $description2
        ];

        $expectedOutput = new \ArrayObject($output);

        $this->assertEquals($expectedOutput, $sut->provide($publication, new \ArrayObject()));
    }
}
