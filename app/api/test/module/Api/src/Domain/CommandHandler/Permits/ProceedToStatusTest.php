<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\ProceedToStatus as ProceedToStatusCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\ProceedToStatus as ProceedToStatusHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Proceed to status test
 */
class ProceedToStatusTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProceedToStatusHandler();
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpPermit::STATUS_AWAITING_PRINTING,
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $p1Id = 10;
        $p2Id = 11;

        $cmdData = [
            'ids' => [$p1Id, $p2Id],
            'status' => IrhpPermit::STATUS_AWAITING_PRINTING,
        ];

        $command = ProceedToStatusCmd::create($cmdData);

        $p1 = m::mock(PermitWindowEntity::class);
        $p1->shouldReceive('getId')
            ->andReturn($p1Id)
            ->shouldReceive('proceedToStatus')
            ->with($this->refData[IrhpPermit::STATUS_AWAITING_PRINTING])
            ->once();

        $p2 = m::mock(PermitWindowEntity::class);
        $p2->shouldReceive('getId')
            ->andReturn($p2Id)
            ->shouldReceive('proceedToStatus')
            ->with($this->refData[IrhpPermit::STATUS_AWAITING_PRINTING])
            ->once();

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchByIds')
            ->with([$p1Id, $p2Id])
            ->andReturn([$p1, $p2])
            ->shouldReceive('refresh')
            ->with($p1)
            ->once()
            ->shouldReceive('save')
            ->with($p1)
            ->once()
            ->shouldReceive('refresh')
            ->with($p2)
            ->once()
            ->shouldReceive('save')
            ->with($p2)
            ->once();

        $this->refData[IrhpPermit::STATUS_AWAITING_PRINTING]->setDescription('Awaiting printing');

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                IrhpPermit::STATUS_AWAITING_PRINTING => [$p1Id, $p2Id]
            ],
            'messages' => ['Permits proceeded to Awaiting printing']
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
