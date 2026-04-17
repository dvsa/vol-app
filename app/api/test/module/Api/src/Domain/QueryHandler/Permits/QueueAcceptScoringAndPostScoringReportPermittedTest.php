<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\QueueAcceptScoringAndPostScoringReportPermitted;
use Dvsa\Olcs\Api\Domain\Query\Permits\QueueAcceptScoringAndPostScoringReportPermitted
    as QueueAcceptScoringAndPostScoringReportPermittedQry;
use Dvsa\Olcs\Api\Domain\Query\Permits\CheckAcceptScoringAndPostScoringReportPrerequisites;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class QueueAcceptScoringAndPostScoringReportPermittedTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);

        $this->sut = m::mock(QueueAcceptScoringAndPostScoringReportPermitted::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('permittedScenariosProvider')]
    public function testHandleQuery(mixed $prerequisiteResult, mixed $prerequisiteMessage): void
    {
        $stockId = 28;

        $stock = m::mock(IrhpPermitStock::class);
        $stock->shouldReceive('statusAllowsQueueAcceptScoringAndPostScoringReport')
            ->andReturn(true);

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with($stockId)
            ->andReturn($stock);

        $queryHandler = m::mock(AbstractQueryHandler::class);

        $queryHandler->shouldReceive('handleQuery')
            ->with(m::type(CheckAcceptScoringAndPostScoringReportPrerequisites::class))
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
            CheckAcceptScoringAndPostScoringReportPrerequisites::create(['id' => $stockId])
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
        $stock->shouldReceive('statusAllowsQueueAcceptScoringAndPostScoringReport')
            ->andReturn(false);
        $stock->shouldReceive('getStatusDescription')
            ->andReturn('stock status description');

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with($stockId)
            ->andReturn($stock);

        $result = $this->sut->handleQuery(
            CheckAcceptScoringAndPostScoringReportPrerequisites::create(['id' => $stockId])
        );

        $this->assertEquals(
            [
                'result' => false,
                'message' => 'Acceptance, stock alignment and post scoring report are not permitted when stock status is \'stock status description\''
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
