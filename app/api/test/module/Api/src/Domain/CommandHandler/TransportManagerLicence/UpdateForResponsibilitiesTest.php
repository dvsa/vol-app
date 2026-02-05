<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerLicence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * TransportManagerLicence / UpdateForResponsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerLicence\UpdateForResponsibilities::class)]
class UpdateForResponsibilitiesTest extends AbstractCommandHandlerTestCase
{
    /** @var CommandHandler\TransportManagerLicence\UpdateForResponsibilities   */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CommandHandler\TransportManagerLicence\UpdateForResponsibilities();

        $this->mockRepo('TransportManagerLicence', Repository\TransportManagerLicence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'tmType'
        ];

        $this->references = [
            OperatingCentreEntity::class => [
                1 => m::mock(OperatingCentreEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 1;
        $data = [
            'id' => $id,
            'version' => 2,
            'tmType' => 'tmType',
            'isOwner' => 1,
            'hoursMon' => 1,
            'hoursTue' => 2,
            'hoursWed' => 3,
            'hoursThu' => 4,
            'hoursFri' => 5,
            'hoursSat' => 6,
            'hoursSun' => 7,
            'additionalInformation' => 'ai',
        ];

        $command = TransferCmd\TransportManagerLicence\UpdateForResponsibilities::create($data);

        $mockTmLicence = m::mock(Entity\Tm\TransportManagerLicence::class)
            ->shouldReceive('updateTransportManagerLicence')
            ->with(
                $this->refData['tmType'],
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                'ai',
                1
            )
            ->once()
            ->shouldReceive('getId')
            ->andReturn($id)
            ->once()
            ->getMock();

        $this->repoMap['TransportManagerLicence']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 2)
            ->andReturn($mockTmLicence)
            ->once()
            ->shouldReceive('save')
            ->with($mockTmLicence)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);
        $this->assertEquals(
            $result->toArray(),
            [
                'id' => [
                    'transportManagerLicence' => 1
                ],
                'messages' => [
                    'Transport Manager Licence updated'
                ]
            ]
        );
    }
}
