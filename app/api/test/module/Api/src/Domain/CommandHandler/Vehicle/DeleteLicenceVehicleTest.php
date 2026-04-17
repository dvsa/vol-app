<?php

declare(strict_types=1);

/**
 * Delete Licence Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CeaseActiveDiscs;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\DeleteLicenceVehicle;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Transfer\Command\Vehicle\DeleteLicenceVehicle as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;

/**
 * Delete Licence Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteLicenceVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteLicenceVehicle();
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [
                111, 222
            ]
        ];
        $command = Cmd::create($data);

        /** @var LicenceVehicleEntity $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle1->setRemovalDate(new \DateTime());

        /** @var LicenceVehicleEntity $licenceVehicle2 */
        $licenceVehicle2 = m::mock(LicenceVehicleEntity::class)->makePartial();

        $result1 = new Result();
        $result1->addMessage('2 Disc(s) Ceased');
        $this->expectedSideEffect(CeaseActiveDiscs::class, $data, $result1);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($licenceVehicle1)
            ->shouldReceive('fetchById')
            ->with(222)
            ->andReturn($licenceVehicle2)
            ->shouldReceive('save')
            ->with($licenceVehicle2);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(date('Y-m-d'), $licenceVehicle2->getRemovalDate()->format('Y-m-d'));

        $expected = [
            'id' => [],
            'messages' => [
                '2 Disc(s) Ceased',
                '1 Vehicle(s) Deleted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
