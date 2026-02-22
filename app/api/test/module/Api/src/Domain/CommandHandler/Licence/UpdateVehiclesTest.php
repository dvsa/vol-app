<?php

declare(strict_types=1);

/**
 * Update Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateVehicles;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateVehicles as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateVehiclesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateVehicles();
        $this->mockRepo('Licence', Repository\Licence::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'shareInfo' => 'Y'
        ];

        $command = Cmd::create($data);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getOrganisation->setConfirmShareVehicleInfo')
            ->once()
            ->with('Y');

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence)
            ->shouldReceive('save')
            ->once()
            ->with($licence);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Organisation updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
