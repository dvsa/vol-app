<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\SelfserveApplicationsSummary;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveApplicationsSummary as SelfserveApplicationsSummaryQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class SelfserveApplicationsSummaryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new SelfserveApplicationsSummary();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $organisationId = 8;

        $rows = [
            'row1',
            'row2',
            'row3',
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('fetchSelfserveApplicationsSummary')
            ->with($organisationId)
            ->andReturn($rows);

        $result = $this->sut->handleQuery(SelfserveApplicationsSummaryQry::create(['organisation' => $organisationId]));
        $this->assertEquals($rows, $result);
    }
}
