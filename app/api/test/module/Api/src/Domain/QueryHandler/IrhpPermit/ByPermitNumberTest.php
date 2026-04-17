<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Query\IrhpPermit\ByPermitNumber as Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermit\ByPermitNumber as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * ByPermitNumber Test
 */
class ByPermitNumberTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $irhpPermitRange = 1;
        $permitNumber = 100;

        $query = Query::create(
            [
                'irhpPermitRange' => $irhpPermitRange,
                'permitNumber' => $permitNumber,
            ]
        );

        $results = [
            ['id' => 1],
            ['id' => 2],
        ];

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchByNumberAndRange')
            ->with($permitNumber, $irhpPermitRange)
            ->once()
            ->andReturn($results);

        self::assertEquals($results, $this->sut->handleQuery($query));
    }
}
