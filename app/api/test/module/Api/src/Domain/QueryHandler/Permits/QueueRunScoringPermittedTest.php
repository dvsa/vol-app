<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\QueueRunScoringPermitted;
use Dvsa\Olcs\Api\Domain\Query\Permits\QueueRunScoringPermitted as QueueRunScoringPermittedQry;
use Dvsa\Olcs\Api\Domain\Query\Permits\CheckRunScoringPrerequisites;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class QueueRunScoringPermittedTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);

        $this->sut = m::mock(QueueRunScoringPermitted::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('permittedScenariosProvider')]
    public function testHandleQuery(mixed $prerequisiteResult, mixed $prerequisiteMessage): void
    {
        $stockId = 28;

        $stock = m::mock(IrhpPermitStock::class);
        $stock->shouldReceive('statusAllowsQueueRunScoring')
            ->andReturn(true);

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with($stockId)
            ->andReturn($stock);

        $queryHandler = m::mock(AbstractQueryHandler::class);
        $queryHandler->shouldReceive('handleQuery')
            ->with(m::type(CheckRunScoringPrerequisites::class))
            ->andReturnUsing(function ($query) use ($stockId, $prerequisiteResult, $prerequisiteMessage) {
                $this->assertEquals($stockId, $query->getId());

                return [
                    'result' => $prerequisiteResult,
                    'message' => $prerequisiteMessage
                ];
            });

        $this->sut->shouldReceive('getQueryHandler')
            ->andReturn($queryHandler);

        $result = $this->sut->handleQuery(
            CheckRunScoringPrerequisites::create(['id' => $stockId])
        );

        $this->assertEquals(
            [
                'result' => $prerequisiteResult,
                'message' => $prerequisiteMessage
            ],
            $result
        );
    }

    public function testHandleQueryUnpermittedStatus(): void
    {
        $stockId = 28;

        $stock = m::mock(IrhpPermitStock::class);
        $stock->shouldReceive('statusAllowsQueueRunScoring')
            ->andReturn(false);
        $stock->shouldReceive('getStatusDescription')
            ->andReturn('stock status description');

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with($stockId)
            ->andReturn($stock);

        $result = $this->sut->handleQuery(
            CheckRunScoringPrerequisites::create(['id' => $stockId])
        );

        $this->assertEquals(
            [
                'result' => false,
                'message' => 'Scoring is not permitted when stock status is \'stock status description\''
            ],
            $result
        );
    }

    public static function permittedScenariosProvider(): array
    {
        return [
            [true, 'Prerequisites ok'],
            [false, 'Prerequisites fail']
        ];
    }
}
