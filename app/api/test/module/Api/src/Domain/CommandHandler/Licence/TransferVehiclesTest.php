<?php

declare(strict_types=1);

/**
 * Transfer Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsDiscs;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\TransferVehicles;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Dvsa\Olcs\Transfer\Command\Licence\TransferVehicles as Cmd;
use Dvsa\Olcs\Transfer\Command\Vehicle\DeleteLicenceVehicle;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;

/**
 * Transfer Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransferVehiclesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new TransferVehicles();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandWillExceed(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'id' => 111,
            'target' => 222,
            'licenceVehicles' => [
                123, 321
            ]
        ];
        $command = Cmd::create($data);

        /** @var Licence $sourceLicence */
        $sourceLicence = m::mock(Licence::class)->makePartial();

        /** @var Licence $targetLicence */
        $targetLicence = m::mock(Licence::class)->makePartial();
        $targetLicence->updateTotAuthHgvVehicles(10);
        $targetLicence->shouldReceive('getActiveVehiclesCount')
            ->andReturn(10);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($sourceLicence)
            ->shouldReceive('fetchById')
            ->with(222)
            ->andReturn($targetLicence);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithOverlappingSingle(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'id' => 111,
            'target' => 222,
            'licenceVehicles' => [
                123
            ]
        ];
        $command = Cmd::create($data);

        /** @var Vehicle $vehicle1 */
        $vehicle1 = m::mock(Vehicle::class)->makePartial();
        $vehicle1->setVrm('ABC123');

        /** @var LicenceVehicleEntity $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);

        $licenceVehicles = [
            $licenceVehicle1
        ];

        /** @var Licence $sourceLicence */
        $sourceLicence = m::mock(Licence::class)->makePartial();
        $sourceLicence->shouldReceive('getLicenceVehicles->matching')
            ->andReturn($licenceVehicles);

        /** @var Licence $targetLicence */
        $targetLicence = m::mock(Licence::class)->makePartial();
        $targetLicence->updateTotAuthHgvVehicles(10);
        $targetLicence->shouldReceive('getActiveVehiclesCount')
            ->andReturn(5)
            ->shouldReceive('getActiveVehicles')
            ->andReturn($licenceVehicles);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($sourceLicence)
            ->shouldReceive('fetchById')
            ->with(222)
            ->andReturn($targetLicence);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithOverlappingMultiple(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'id' => 111,
            'target' => 222,
            'licenceVehicles' => [
                123, 321
            ]
        ];
        $command = Cmd::create($data);

        /** @var Vehicle $vehicle2 */
        $vehicle2 = m::mock(Vehicle::class)->makePartial();
        $vehicle2->setVrm('ABC124');

        /** @var LicenceVehicleEntity $licenceVehicle2 */
        $licenceVehicle2 = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle2->setVehicle($vehicle2);

        /** @var Vehicle $vehicle1 */
        $vehicle1 = m::mock(Vehicle::class)->makePartial();
        $vehicle1->setVrm('ABC123');

        /** @var LicenceVehicleEntity $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);

        $licenceVehicles = [
            $licenceVehicle1,
            $licenceVehicle2
        ];

        /** @var Licence $sourceLicence */
        $sourceLicence = m::mock(Licence::class)->makePartial();
        $sourceLicence->shouldReceive('getLicenceVehicles->matching')
            ->andReturn($licenceVehicles);

        /** @var Licence $targetLicence */
        $targetLicence = m::mock(Licence::class)->makePartial();
        $targetLicence->updateTotAuthHgvVehicles(10);
        $targetLicence->shouldReceive('getActiveVehiclesCount')
            ->andReturn(5)
            ->shouldReceive('getActiveVehicles')
            ->andReturn($licenceVehicles);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($sourceLicence)
            ->shouldReceive('fetchById')
            ->with(222)
            ->andReturn($targetLicence);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'target' => 222,
            'licenceVehicles' => [
                123, 321
            ]
        ];
        $command = Cmd::create($data);

        /** @var Vehicle $vehicle2 */
        $vehicle2 = m::mock(Vehicle::class)->makePartial();
        $vehicle2->setVrm('ABC124');

        /** @var LicenceVehicleEntity $licenceVehicle2 */
        $licenceVehicle2 = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle2->setVehicle($vehicle2);

        /** @var Vehicle $vehicle1 */
        $vehicle1 = m::mock(Vehicle::class)->makePartial();
        $vehicle1->setVrm('ABC123');

        /** @var LicenceVehicleEntity $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);

        $licenceVehicles = [
            $licenceVehicle1,
            $licenceVehicle2
        ];

        /** @var Licence $sourceLicence */
        $sourceLicence = m::mock(Licence::class)->makePartial();
        $sourceLicence->shouldReceive('getLicenceVehicles->matching')
            ->andReturn($licenceVehicles);

        /** @var Licence $targetLicence */
        $targetLicence = m::mock(Licence::class)->makePartial();
        $targetLicence->updateTotAuthHgvVehicles(10);
        $targetLicence->shouldReceive('getActiveVehiclesCount')
            ->andReturn(5)
            ->shouldReceive('getActiveVehicles')
            ->andReturn([]);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($sourceLicence)
            ->shouldReceive('fetchById')
            ->with(222)
            ->andReturn($targetLicence);

        $data = ['ids' => [123, 321]];
        $result1 = new Result();
        $result1->addMessage('Vehicles deleted');
        $this->expectedSideEffect(DeleteLicenceVehicle::class, $data, $result1);

        $savedLicenceVehicles = [];

        $this->repoMap['LicenceVehicle']->shouldReceive('save')
            ->with(m::type(LicenceVehicleEntity::class))
            ->andReturnUsing(
                function (LicenceVehicleEntity $licenceVehicle) use (&$savedLicenceVehicles) {
                    $savedLicenceVehicles[] = $licenceVehicle;
                    $licenceVehicle->setId('33' . count($savedLicenceVehicles));
                }
            );

        $data = [
            'ids' => [331, 332],
            'isCopy' => 'N'
        ];
        $result2 = new Result();
        $result2->addMessage('Discs created');
        $this->expectedSideEffect(CreateGoodsDiscs::class, $data, $result2);

        $this->expectedLicenceCacheClear($sourceLicence);
        $this->expectedLicenceCacheClear($targetLicence);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Vehicles deleted',
                '2 Licence Vehicle(s) created',
                'Discs created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertCount(2, $savedLicenceVehicles);

        foreach ($savedLicenceVehicles as $savedLicenceVehicle) {
            $this->assertEquals(date('Y-m-d'), $savedLicenceVehicle->getSpecifiedDate()->format('Y-m-d'));
            $this->assertSame($targetLicence, $savedLicenceVehicle->getLicence());
        }
    }
}
