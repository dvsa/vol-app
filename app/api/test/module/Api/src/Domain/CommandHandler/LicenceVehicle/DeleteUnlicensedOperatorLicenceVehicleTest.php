<?php

declare(strict_types=1);

/**
 * Delete Unlicensed Operator Licence Vehicle Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceVehicle;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceVehicle\DeleteUnlicensedOperatorLicenceVehicle as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Api\Domain\Repository\Vehicle as VehicleRepo;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle as VehicleEntity;
use Dvsa\Olcs\Transfer\Command\LicenceVehicle\DeleteUnlicensedOperatorLicenceVehicle as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Delete Unlicensed Operator Licence Vehicle Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class DeleteUnlicensedOperatorLicenceVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 69;

        $command = Cmd::create(
            [
                'id' => $id,
            ]
        );

        $licenceVehicle = m::mock(LicenceVehicleEntity::class);

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licenceVehicle);

        $licenceVehicle
            ->shouldReceive('getId')
            ->once()
            ->andReturn($id);

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('delete')
            ->with($licenceVehicle)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'licenceVehicle' => $id,
            ],
            $result->getIds()
        );

        $this->assertEquals(
            [
                'LicenceVehicle deleted',
            ],
            $result->getMessages()
        );
    }
}
