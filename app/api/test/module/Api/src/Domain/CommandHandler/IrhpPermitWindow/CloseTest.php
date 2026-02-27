<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermitWindow\Close as CloseCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitWindow\Close as CloseHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as PermitWindowRepo;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as PermitWindowEntity;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CancelApplication as CancelIrhpApplication;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Close IRHP Permit Window Test
 */
class CloseTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CloseHandler();
        $this->mockRepo('IrhpPermitWindow', PermitWindowRepo::class);
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $windowId = 1;
        $irhpApp1Id = 14;
        $irhpApp2Id = 15;

        $cmdData = [
            'id' => $windowId,
        ];

        $command = CloseCmd::create($cmdData);

        $window = m::mock(PermitWindowEntity::class);
        $window->shouldReceive('hasEnded')
            ->andReturn(true);
        $window->shouldReceive('getId')
            ->andReturn($windowId);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchById')
            ->with($cmdData['id'])
            ->andReturn($window);

        $irhpApp1 = m::mock(IrhpApplication::class);
        $irhpApp1->shouldReceive('getId')
            ->andReturn($irhpApp1Id);

        $irhpApp2 = m::mock(IrhpApplication::class);
        $irhpApp2->shouldReceive('getId')
            ->andReturn($irhpApp2Id);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchByWindowId')
            ->with($windowId, [IrhpInterface::STATUS_NOT_YET_SUBMITTED])
            ->andReturn([$irhpApp1, $irhpApp2]);

        $this->expectedSideEffect(
            CancelIrhpApplication::class,
            [
                'id' => $irhpApp1Id,
            ],
            (new Result())->addMessage('IRHP App1 has been cancelled')
        );

        $this->expectedSideEffect(
            CancelIrhpApplication::class,
            [
                'id' => $irhpApp2Id,
            ],
            (new Result())->addMessage('IRHP App2 has been cancelled')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'id' => $windowId
            ],
            'messages' => [
                'IRHP App1 has been cancelled',
                'IRHP App2 has been cancelled',
                sprintf('IRHP permit window \'%d\' has been closed', $windowId)
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleWindowOpen(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $this->expectExceptionMessage('Window which has not ended cannot be closed');

        $cmdData = [
            'id' => 1,
        ];

        $command = CloseCmd::create($cmdData);

        $permitWindowEntity = m::mock(PermitWindowEntity::class);
        $permitWindowEntity
            ->shouldReceive('hasEnded')
            ->andReturn(false);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchById')
            ->with($cmdData['id'])
            ->andReturn($permitWindowEntity);

        $this->sut->handleCommand($command);
    }
}
