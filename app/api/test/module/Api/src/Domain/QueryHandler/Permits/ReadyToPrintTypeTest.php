<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\ReadyToPrintType;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as IrhpPermitTypeRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintType as ReadyToPrintTypeQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class ReadyToPrintTypeTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ReadyToPrintType();
        $this->mockRepo('IrhpPermitType', IrhpPermitTypeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $irhpPermitTypes = [
            m::mock(IrhpPermitType::class),
            m::mock(IrhpPermitType::class),
            m::mock(IrhpPermitType::class)
        ];

        $query = ReadyToPrintTypeQuery::create([]);

        $this->repoMap['IrhpPermitType']->shouldReceive('fetchReadyToPrint')
            ->withNoArgs()
            ->andReturn($irhpPermitTypes);

        $this->assertEquals(
            ['results' => $irhpPermitTypes],
            $this->sut->handleQuery($query)
        );
    }
}
