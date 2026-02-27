<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\ReadyToPrintRangeType;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintRangeType as ReadyToPrintRangeTypeQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class ReadyToPrintRangeTypeTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ReadyToPrintRangeType();
        $this->mockRepo('IrhpPermitRange', IrhpPermitRangeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $irhpPermitStockId = 100;
        $irhpPermitRangeTypes = [
            IrhpPermitRange::BILATERAL_TYPE_STANDARD_SINGLE,
        ];

        $query = ReadyToPrintRangeTypeQuery::create(['irhpPermitStock' => $irhpPermitStockId]);

        $this->repoMap['IrhpPermitRange']->shouldReceive('fetchReadyToPrint')
            ->with($irhpPermitStockId)
            ->andReturn($irhpPermitRangeTypes);

        $this->assertEquals(
            ['results' => $irhpPermitRangeTypes],
            $this->sut->handleQuery($query)
        );
    }
}
