<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\PsvDiscCount as QryHandler;
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc;
use Dvsa\Olcs\Transfer\Query\Licence\PsvDiscCount;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class PsvDiscCountTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QryHandler();
        $this->mockRepo('PsvDisc', PsvDisc::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = PsvDiscCount::create(['id' => 1]);

        $this->repoMap['PsvDisc']->shouldReceive('countForLicence')
            ->once()
            ->with(1)
            ->andReturn(4);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(4, $result);
    }
}
