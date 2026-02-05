<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Surrender;

use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Surrender\OpenBusReg as QryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Entity\View\BusRegSearchView;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenBusReg;
use Mockery as m;

class OpenBusRegTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QryHandler();
        $this->mockRepo('BusRegSearchView', CasesRepo::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = OpenBusReg::create(['licId' => 1]);

        $expected = [
            m::mock(BusRegSearchView::class)
                ->shouldReceive('serialize')->once()->andReturn('foo')->getMock()
        ];

        $this->repoMap['BusRegSearchView']->shouldReceive(
            'fetchActiveByLicence'
        )->andReturn($expected);

        $this->assertEquals([
            'count' => 1,
            'results' => ['foo']
        ], $this->sut->handleQuery($query));
    }
}
