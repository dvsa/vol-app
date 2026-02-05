<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerApplication;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Doctrine\ORM\Query;

/**
 * TransportManagerApplication / UpdateForResponsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication\UpdateForResponsibilities::class)]
class UpdateForResponsibilitiesTest extends AbstractCommandHandlerTestCase
{
    /** @var CommandHandler\TransportManagerApplication\UpdateForResponsibilities */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CommandHandler\TransportManagerApplication\UpdateForResponsibilities();

        $this->mockRepo('TransportManagerApplication', Repository\TransportManagerApplication::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'tmType', 'tmAppStatus'
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
            'hasUndertakenTraining' => 'Y',
            'hoursMon' => 1,
            'hoursTue' => 2,
            'hoursWed' => 3,
            'hoursThu' => 4,
            'hoursFri' => 5,
            'hoursSat' => 6,
            'hoursSun' => 7,
            'additionalInformation' => 'ai',
            'tmApplicationStatus' => 'tmAppStatus',
            'operatingCentres' => [1]
        ];

        $command = TransferCmd\TransportManagerApplication\UpdateForResponsibilities::create($data);

        $mockTmApplication = m::mock(Entity\Tm\TransportManagerApplication::class)
            ->shouldReceive('updateTransportManagerApplicationFull')
            ->with(
                $this->refData['tmType'],
                1,
                'Y',
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                'ai',
                $this->refData['tmAppStatus']
            )
            ->once()
            ->shouldReceive('getId')
            ->andReturn($id)
            ->once()
            ->getMock();

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 2)
            ->andReturn($mockTmApplication)
            ->once()
            ->shouldReceive('save')
            ->with($mockTmApplication)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);
        $this->assertEquals(
            $result->toArray(),
            [
                'id' => [
                    'transportManagerApplication' => 1
                ],
                'messages' => [
                    'Transport Manager Application updated'
                ]
            ]
        );
    }
}
