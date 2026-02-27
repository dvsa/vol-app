<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\MaxStockPermitsByApplication;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermits as MaxStockPermitsQry;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermitsByApplication as MaxStockPermitsByApplicationQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class MaxStockPermitsByApplicationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(MaxStockPermitsByApplication::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $irhpApplicationId = 5;
        $licenceId = 8;

        $expectedResult = [
            'result' => [
                7 => 5,
                8 => 10,
                9 => 15
            ]
        ];

        $queryHandler = m::mock(AbstractQueryHandler::class);
        $queryHandler->shouldReceive('handleQuery')
            ->with(m::type(MaxStockPermitsQry::class))
            ->andReturnUsing(function ($query) use ($licenceId, $expectedResult) {
                $this->assertEquals($licenceId, $query->getLicence());

                return $expectedResult;
            });

        $this->sut->shouldReceive('getQueryHandler')
            ->andReturn($queryHandler);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getLicence->getId')
            ->andReturn($licenceId);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $result = $this->sut->handleQuery(MaxStockPermitsByApplicationQry::create(['id' => $irhpApplicationId]));
        $this->assertEquals($expectedResult, $result);
    }
}
