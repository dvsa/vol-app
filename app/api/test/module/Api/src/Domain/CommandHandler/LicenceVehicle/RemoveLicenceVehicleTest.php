<?php

declare(strict_types=1);

/**
 * RemoveLicenceVehicleTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Command\LicenceVehicle\RemoveLicenceVehicle as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceVehicle\RemoveLicenceVehicle;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Class RemoveLicenceVehicleTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceStatusRule
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class RemoveLicenceVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RemoveLicenceVehicle();
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licence' => 123
        ];

        $command = Cmd::create($data);

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('removeAllForLicence')
            ->once()
            ->with(123);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
            ],
            'messages' => [
                'Removed vehicles for licence.'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
