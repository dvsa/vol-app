<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\CheckAcceptScoringAndPostScoringReportPrerequisites;
use Dvsa\Olcs\Api\Domain\Query\Permits\CheckAcceptScoringAndPostScoringReportPrerequisites
    as CheckAcceptScoringAndPostScoringReportPrerequisitesQry;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class CheckAcceptScoringAndPostScoringReportPrerequisitesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CheckAcceptScoringAndPostScoringReportPrerequisites();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitRange', IrhpPermitRangeRepo::class);
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('scenariosProvider')]
    public function testHandleQuery(
        mixed $euro5CombinedRangeSize,
        mixed $euro5PermitCount,
        mixed $euro5SuccessfulCount,
        mixed $euro6CombinedRangeSize,
        mixed $euro6PermitCount,
        mixed $euro6SuccessfulCount,
        mixed $expectedResult,
        mixed $expectedMessage
    ): void {
        $stockId = 25;

        $this->repoMap['IrhpPermitRange']->shouldReceive('getCombinedRangeSize')
            ->with($stockId, RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5CombinedRangeSize);

        $this->repoMap['IrhpPermit']->shouldReceive('getPermitCount')
            ->with($stockId, RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5PermitCount);

        $this->repoMap['IrhpApplication']->shouldReceive('hasInScopeUnderConsiderationApplications')
            ->with($stockId)
            ->andReturn(true);

        $this->repoMap['IrhpApplication']->shouldReceive('getSuccessfulCountInScope')
            ->with($stockId, RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5SuccessfulCount);

        $this->repoMap['IrhpPermitRange']->shouldReceive('getCombinedRangeSize')
            ->with($stockId, RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6CombinedRangeSize);

        $this->repoMap['IrhpPermit']->shouldReceive('getPermitCount')
            ->with($stockId, RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6PermitCount);

        $this->repoMap['IrhpApplication']->shouldReceive('getSuccessfulCountInScope')
            ->with($stockId, RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6SuccessfulCount);

        $result = $this->sut->handleQuery(
            CheckAcceptScoringAndPostScoringReportPrerequisitesQry::create(['id' => $stockId])
        );

        $this->assertEquals(
            [
                'result' => $expectedResult,
                'message' => $expectedMessage
            ],
            $result
        );
    }

    public static function scenariosProvider(): array
    {
        return [
            [null, 0, 5, 20, 10, 5, false, '5 Euro 5 permits required but no Euro 5 ranges available'],
            [10, 7, 5, 20, 10, 5, false, 'Insufficient Euro 5 permits available - 3 available, 5 required'],
            [10, 5, 3, null, 0, 4, false, '4 Euro 6 permits required but no Euro 6 ranges available'],
            [10, 5, 3, 15, 11, 6, false, 'Insufficient Euro 6 permits available - 4 available, 6 required'],
            [10, 5, 3, 15, 8, 6, true, 'Prerequisites passed'],
            [null, 0, 3, 15, 8, 0, false, '3 Euro 5 permits required but no Euro 5 ranges available'],
            [20, 12, 10, 15, 8, 0, false, 'Insufficient Euro 5 permits available - 8 available, 10 required'],
            [20, 7, 10, 15, 8, 0, true, 'Prerequisites passed'],
            [20, 7, 0, null, 0, 11, false, '11 Euro 6 permits required but no Euro 6 ranges available'],
            [20, 7, 0, 15, 8, 9, false, 'Insufficient Euro 6 permits available - 7 available, 9 required'],
            [20, 7, 0, 15, 4, 9, true, 'Prerequisites passed'],
        ];
    }

    public function testHandleQueryNoInScopeUnderConsiderationApplications(): void
    {
        $stockId = 37;

        $this->repoMap['IrhpApplication']
            ->shouldReceive('hasInScopeUnderConsiderationApplications')
            ->with($stockId)
            ->andReturn(false);

        $result = $this->sut->handleQuery(
            CheckAcceptScoringAndPostScoringReportPrerequisitesQry::create(['id' => $stockId])
        );

        $this->assertEquals(
            [
                'result' => false,
                'message' => 'No under consideration applications currently in scope'
            ],
            $result
        );
    }
}
