<?php

declare(strict_types=1);

/**
 * ShortNoticeByBusReg Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\ResultList;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\ShortNoticeByBusReg;
use Dvsa\Olcs\Api\Entity\Bus\BusShortNotice as ShortNoticeEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\BusShortNotice as ShortNoticeRepo;
use Dvsa\Olcs\Transfer\Query\Bus\ShortNoticeByBusReg as Qry;
use Mockery as m;

/**
 * ShortNoticeByBusReg Test
 */
class ShortNoticeByBusRegTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ShortNoticeByBusReg();
        $this->mockRepo('BusShortNotice', ShortNoticeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['BusShortNotice']->shouldReceive('fetchByBusReg')
            ->with($query)
            ->andReturn([$mockResult]);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['result'], ['foo']);
    }
}
