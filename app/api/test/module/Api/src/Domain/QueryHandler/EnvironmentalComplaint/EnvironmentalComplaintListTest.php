<?php

declare(strict_types=1);

/**
 * EnvironmentalComplaintList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\EnvironmentalComplaint;

use Dvsa\Olcs\Api\Domain\QueryHandler\EnvironmentalComplaint\EnvironmentalComplaintList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Complaint as ComplaintRepo;
use Dvsa\Olcs\Transfer\Query\EnvironmentalComplaint\EnvironmentalComplaintList as Qry;
use Mockery as m;

/**
 * EnvironmentalComplaintList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class EnvironmentalComplaintListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EnvironmentalComplaintList();
        $this->mockRepo('Complaint', ComplaintRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['Complaint']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['Complaint']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], 2);
        $this->assertEquals($result['result'], ['foo']);
    }
}
