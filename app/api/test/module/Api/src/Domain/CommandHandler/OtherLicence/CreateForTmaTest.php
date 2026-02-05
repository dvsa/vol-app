<?php

declare(strict_types=1);

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence\CreateForTma as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as OtherLicenceEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateForTma as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateForTmaTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('OtherLicence', OtherLicence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'A-ROLE',
        ];

        $this->references = [
            TransportManagerApplication::class => [
                12 => m::mock(TransportManagerApplication::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(
            [
                'role' => 'A-ROLE',
                'tmaId' => 12,
                'hoursPerWeek' => 12,
                'licNo' => 'LIC001',
                'operatingCentres' => 'oc',
                'totalAuthVehicles' => 64
            ]
        );

        $this->repoMap['OtherLicence']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (OtherLicenceEntity $ol) {
                    $ol->setId(422);
                    $this->assertSame(
                        $this->references[TransportManagerApplication::class][12],
                        $ol->getTransportManagerApplication()
                    );
                    $this->assertSame($this->refData['A-ROLE'], $ol->getRole());
                    $this->assertSame(12, $ol->getHoursPerWeek());
                    $this->assertSame('LIC001', $ol->getLicNo());
                    $this->assertSame('oc', $ol->getOperatingCentres());
                    $this->assertSame(64, $ol->getTotalAuthVehicles());
                }
            );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['otherLicence' => 422], $response->getIds());
        $this->assertSame(['Other Licence ID 422 created'], $response->getMessages());
    }
}
