<?php

declare(strict_types=1);

/**
 * Update Unlicensed Operator Licence Vehicle Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceVehicle;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceVehicle\UpdateUnlicensedOperatorLicenceVehicle as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle as VehicleEntity;
use Dvsa\Olcs\Transfer\Command\LicenceVehicle\UpdateUnlicensedOperatorLicenceVehicle as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Unlicensed Operator Licence Vehicle Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UpdateUnlicensedOperatorLicenceVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        parent::setUp();
    }

    public function testHandleCommandGoods(): void
    {
        $id = 69;
        $version = 2;

        $command = Cmd::create(
            [
                'id' => $id,
                'version' => $version,
                'vrm' => 'ABC1234',
                'platedWeight' => 895,
            ]
        );

        $licenceVehicle = m::mock(LicenceVehicleEntity::class);

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($licenceVehicle);

        $licenceVehicle
            ->shouldReceive('getId')
            ->andReturn($id);

        $licenceVehicle
            ->shouldReceive('getVehicle->setVrm')
            ->with('ABC1234')
            ->once();

        $licenceVehicle
            ->shouldReceive('getVehicle->setPlatedWeight')
            ->with(895)
            ->once();

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('save')
            ->with($licenceVehicle)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['licenceVehicle' => $id],
            $result->getIds()
        );

        $this->assertEquals(
            ['LicenceVehicle updated'],
            $result->getMessages()
        );
    }

    public function testHandleCommandPsv(): void
    {
        $id = 69;
        $version = 2;

        $command = Cmd::create(
            [
                'id' => $id,
                'version' => $version,
                'vrm' => 'ABC1234',
            ]
        );

        $licenceVehicle = m::mock(LicenceVehicleEntity::class);

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($licenceVehicle);

        $licenceVehicle
            ->shouldReceive('getId')
            ->andReturn($id);

        $licenceVehicle
            ->shouldReceive('getVehicle->setVrm')
            ->with('ABC1234')
            ->once();

        $licenceVehicle
            ->shouldReceive('getVehicle->setPlatedWeight')
            ->with(null)
            ->once();

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('save')
            ->with($licenceVehicle)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['licenceVehicle' => $id],
            $result->getIds()
        );

        $this->assertEquals(
            ['LicenceVehicle updated'],
            $result->getMessages()
        );
    }
}
