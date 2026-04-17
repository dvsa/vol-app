<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\GoodsDiscCount as QryHandler;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc;
use Dvsa\Olcs\Transfer\Query\Licence\GoodsDiscCount;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class GoodsDiscCountTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QryHandler();
        $this->mockRepo('GoodsDisc', GoodsDisc::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = GoodsDiscCount::create(['id' => 1]);

        $this->repoMap['GoodsDisc']->shouldReceive('countForLicence')
            ->once()
            ->with(1)
            ->andReturn(4);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(4, $result);
    }
}
