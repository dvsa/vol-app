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
final class LocalAuthorityListTest extends QueryHandlerTestCase
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

        $darlington = m::mock();
        $darlington->expects('serialize')->with(['trafficArea'])->andReturn(['id' => 36, 'description' => 'Darlington']);
        $teesValley = m::mock();
        $teesValley->expects('serialize')->with(['trafficArea'])->andReturn(['id' => 193, 'description' => 'Tees Valley']);

        $this->repoMap['LocalAuthority']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$darlington, $teesValley]);

        $this->repoMap['LocalAuthority']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn($count);

        $this->repoMap['LocalAuthority']->expects('fetchSupersededIds')->withNoArgs()->andReturn([36]);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], $count);
        // Replaced authorities are flagged so that lists can leave them out
        $this->assertSame(
            [
                ['id' => 36, 'description' => 'Darlington', 'isSuperseded' => true],
                ['id' => 193, 'description' => 'Tees Valley', 'isSuperseded' => false],
            ],
            $result['result']
        );
    }
}
