<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\Query\IrhpPermitRange\ByPermitNumber as Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitRange\ByPermitNumber as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as PermitRangeRepo;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * ByPermitNumber Test
 */
class ByPermitNumberTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('IrhpPermitRange', PermitRangeRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $permitStock = 1;
        $permitNumber = 100;

        $query = Query::create(
            [
                'permitStock' => $permitStock,
                'permitNumber' => $permitNumber,
            ]
        );

        $permitRanges = [
            ['id' => 1],
            ['id' => 2],
        ];

        $this->repoMap['IrhpPermitRange']
            ->shouldReceive('fetchByPermitNumberAndStock')
            ->with($permitNumber, $permitStock)
            ->once()
            ->andReturn($permitRanges);

        $result = $this->sut->handleQuery($query);

        self::assertEquals($permitRanges, $result);
    }
}
