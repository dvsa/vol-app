<?php

declare(strict_types=1);

/**
 * Local Authority List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\LocalAuthority;

use Dvsa\Olcs\Api\Domain\QueryHandler\LocalAuthority\LocalAuthorityList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\LocalAuthority as LocalAuthorityRepo;
use Dvsa\Olcs\Transfer\Query\LocalAuthority\LocalAuthorityList as Qry;
use Mockery as m;

/**
 * Local Authority List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LocalAuthorityListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new LocalAuthorityList();
        $this->mockRepo('LocalAuthority', LocalAuthorityRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $count = 25;
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['LocalAuthority']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['LocalAuthority']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn($count);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], $count);
        $this->assertEquals($result['result'], ['foo']);
    }
}
