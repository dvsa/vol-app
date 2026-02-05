<?php

declare(strict_types=1);

/**
 * Venue List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Venue;

use Dvsa\Olcs\Api\Domain\QueryHandler\Venue\VenueList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Venue as VenueRepo;
use Dvsa\Olcs\Transfer\Query\Venue\VenueList as Qry;
use Mockery as m;

/**
 * Venue List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VenueListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new VenueList();
        $this->mockRepo('Venue', VenueRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $count = 25;
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['Venue']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['Venue']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn($count);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], $count);
        $this->assertEquals($result['result'], ['foo']);
    }
}
