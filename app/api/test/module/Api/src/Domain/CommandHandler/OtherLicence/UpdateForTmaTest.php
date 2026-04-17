<?php

declare(strict_types=1);

/**
 * UpdateForTmaTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence\UpdateForTma as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as OtherLicenceEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\OtherLicence\UpdateForTma as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * UpdateForTmaTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateForTmaTest extends AbstractCommandHandlerTestCase
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
                'id' => 7687,
                'version' => 44,
                'licNo' => 'LIC001',
                'role' => 'A-ROLE',
                'operatingCentres' => 'oc',
                'totalAuthVehicles' => 64,
                'hoursPerWeek' => 12,
                'holderName' => 'Fred',
            ]
        );

        $otherLicence = new OtherLicenceEntity();

        $this->repoMap['OtherLicence']
            ->shouldReceive('fetchUsingId')->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 44)->once()
            ->andReturn($otherLicence);

        $this->repoMap['OtherLicence']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (OtherLicenceEntity $ol) {
                    $this->assertSame('LIC001', $ol->getLicNo());
                    $this->assertSame($this->refData['A-ROLE'], $ol->getRole());
                    $this->assertSame('oc', $ol->getOperatingCentres());
                    $this->assertSame(64, $ol->getTotalAuthVehicles());
                    $this->assertSame(12, $ol->getHoursPerWeek());
                    $this->assertSame('Fred', $ol->getHolderName());
                }
            );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['Other Licence ID 7687 has been updated'], $response->getMessages());
    }
}
