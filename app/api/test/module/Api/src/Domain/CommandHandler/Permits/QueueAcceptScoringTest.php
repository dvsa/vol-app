<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Query\Permits\QueueAcceptScoringAndPostScoringReportPermitted;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\QueueAcceptScoring;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class QueueAcceptScoringTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('IrhpPermitStock', IrhpPermitStock::class);

        $this->sut = m::mock(QueueAcceptScoring::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpPermitStockEntity::STATUS_ACCEPT_PENDING
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $stockId = 47;

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($stockId);

        $this->sut->shouldReceive('handleQuery')
            ->andReturnUsing(function ($query) use ($stockId) {
                $this->assertInstanceOf(QueueAcceptScoringandPostScoringReportPermitted::class, $query);
                $this->assertEquals($stockId, $query->getId());

                return [
                    'result' => true,
                    'message' => 'ok'
                ];
            });

        $this->expectedQueueSideEffect($stockId, Queue::TYPE_ACCEPT_ECMT_SCORING, []);

        $stock = m::mock(IrhpPermitStockEntity::class);
        $stock->shouldReceive('proceedToAcceptPending')
            ->with($this->refData[IrhpPermitStockEntity::STATUS_ACCEPT_PENDING])
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with($stockId)
            ->andReturn($stock);

        $this->repoMap['IrhpPermitStock']->shouldReceive('save')
            ->with($stock)
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['Queueing accept scoring of ECMT applications'],
            $result->getMessages()
        );
    }

    public function testHandleCommandPermittedQueryFailed(): void
    {
        $stockId = 47;

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($stockId);

        $this->sut->shouldReceive('handleQuery')
            ->andReturnUsing(function ($query) use ($stockId) {
                $this->assertInstanceOf(QueueAcceptScoringAndPostScoringReportPermitted::class, $query);
                $this->assertEquals($stockId, $query->getId());

                return [
                    'result' => false,
                    'message' => 'prerequisites failed'
                ];
            });

        $this->sut->handleCommand($command);
    }
}
