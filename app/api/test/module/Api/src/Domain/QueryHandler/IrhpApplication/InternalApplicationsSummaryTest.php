<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\InternalApplicationsSummary;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\InternalApplicationsSummary as InternalApplicationsSummaryQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class InternalApplicationsSummaryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new InternalApplicationsSummary();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $licenceId = 10;
        $status = IrhpInterface::STATUS_NOT_YET_SUBMITTED;

        $rows = [
            'row1',
            'row2',
            'row3',
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('fetchInternalApplicationsSummary')
            ->with($licenceId, $status)
            ->andReturn($rows);

        $result = $this->sut->handleQuery(
            InternalApplicationsSummaryQry::create(
                ['licence' => $licenceId, 'status' => $status]
            )
        );
        $this->assertEquals($rows, $result);
    }
}
