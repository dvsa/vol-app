<?php

declare(strict_types=1);

/**
 * PublicationLinkByTm Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Publication;

use Dvsa\Olcs\Api\Domain\QueryHandler\Publication\PublicationLinkByTm;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLinkTmList as Qry;
use Mockery as m;

/**
 * PublicationLinkByTm Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationLinkByTmTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PublicationLinkByTm();
        $this->mockRepo('PublicationLink', PublicationLinkRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $count = 25;
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['PublicationLink']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['PublicationLink']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn($count);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], $count);
        $this->assertEquals($result['result'], ['foo']);
    }
}
