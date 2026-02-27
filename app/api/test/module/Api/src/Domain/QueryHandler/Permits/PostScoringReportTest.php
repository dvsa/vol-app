<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\PostScoringReport as PostScoringReportHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Transfer\Query\Permits\PostScoringReport as PostScoringReportQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class PostScoringReportTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PostScoringReportHandler();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $stockId = 99;

        $application27Id = 27;
        $application14Id = 14;
        $application62Id = 62;
        $application48Id = 48;

        $inScopeUnderConsiderationApplicationIds = [
            $application27Id,
            $application14Id,
            $application62Id,
            $application48Id
        ];

        $application27 = m::mock(IrhpApplication::class);
        $application27->shouldReceive('hasStateRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnTrue();
        $application14 = m::mock(IrhpApplication::class);
        $application14->shouldReceive('hasStateRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnFalse();
        $application62 = m::mock(IrhpApplication::class);
        $application62->shouldReceive('hasStateRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnTrue();
        $application48 = m::mock(IrhpApplication::class);
        $application48->shouldReceive('hasStateRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnFalse();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchInScopeUnderConsiderationApplicationIds')
            ->with($stockId)
            ->andReturn($inScopeUnderConsiderationApplicationIds);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($application27Id)
            ->andReturn($application27);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($application14Id)
            ->andReturn($application14);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($application62Id)
            ->andReturn($application62);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($application48Id)
            ->andReturn($application48);

        $expectedResult = [
            'rows' => [
                [$application27Id],
                [$application62Id]
            ]
        ];

        $result = $this->sut->handleQuery(
            PostScoringReportQry::create(
                [
                    'id' => $stockId
                ]
            )
        );

        $this->assertEquals($expectedResult, $result);
    }
}
