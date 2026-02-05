<?php

declare(strict_types=1);

/**
 * PublicationLinkByLicence Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Publication;

use Dvsa\Olcs\Api\Domain\QueryHandler\Publication\PublicationLinkList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLinkList as Qry;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;

/**
 * PublicationLinkByLicence Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationLinkListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PublicationLinkList();
        $this->mockRepo('PublicationLink', PublicationLinkRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

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

    public function testHandleQueryWithApplication(): void
    {
        $count = 25;
        $query = Qry::create(['application' => 1]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $mockApplication = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(2)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchWithLicence')
            ->with(1)
            ->andReturn($mockApplication)
            ->once()
            ->getMock();

        $this->repoMap['PublicationLink']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['PublicationLink']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn($count);

        $result = $this->sut->handleQuery($query);
        $this->assertNull($query->getApplication());
        $this->assertEquals(2, $query->getLicence());
        $this->assertEquals($result['count'], $count);
        $this->assertEquals($result['result'], ['foo']);
    }
}
