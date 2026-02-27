<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\RemoveDuplicateVehicle;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\RemoveDuplicateVehicle as Cmd;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Remove duplicate vehicle test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class RemoveDuplicateVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RemoveDuplicateVehicle();

        $this->mockRepo('LicenceVehicle', LicenceVehicle::class);
        $this->mockRepo('GoodsDisc', GoodsDisc::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 111]);

        /** @var LicenceVehicleEntity $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicleEntity::class)
            ->shouldReceive('setRemovalDate')
            ->with(m::type(DateTime::class))
            ->once()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->twice()
            ->getMock();

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licenceVehicle)
            ->once()
            ->shouldReceive('save')
            ->with($licenceVehicle)
            ->once();

        $this->repoMap['GoodsDisc']
            ->shouldReceive('ceaseDiscsForLicenceVehicle')
            ->with(1)
            ->andReturn(1)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['Goods discs ceased for licence vehicle: 1']
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
