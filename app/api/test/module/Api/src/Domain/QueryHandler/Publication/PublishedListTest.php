<?php

declare(strict_types=1);

/**
 * PendingList Test
 *
 * @author Richard Ward <richard.ward@bjss.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Publication;

use Dvsa\Olcs\Api\Domain\QueryHandler\Publication\PublishedList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Transfer\Query\Publication\PublishedList as Qry;
use Mockery as m;

/**
 * PendingList Test
 *
 * @author Richard Ward <richard.ward@bjss.com>
 */
class PublishedListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PublishedList();
        $this->mockRepo('Publication', PublicationRepo::class);

        parent::setUp();
    }

    /**
     * tests retrieving a list of published publications (status published)
     */
    public function testHandleQuery(): void
    {
        $count = 25;
        $query = Qry::create(
            [
                'pubType' => 'DUMMY_PUB_TYPE',
                'pubDateFrom' => 'DUMMY_PUB_DATE_FROM',
                'pubDateTo' => 'DUMMY_PUB_DATE_TO',
                'trafficArea' => 'DUMMY_TRAFFIC_AREA',
            ]
        );
        $serializedResult = 'foo';

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn($serializedResult);

        $queryResult = [
            'results' => [0 => $mockResult],
            'count' => $count
        ];

        $this->repoMap['Publication']->shouldReceive('fetchPublishedList')
            ->with($query, 'DUMMY_PUB_TYPE', 'DUMMY_PUB_DATE_FROM', 'DUMMY_PUB_DATE_TO', 'DUMMY_TRAFFIC_AREA')
            ->andReturn($queryResult);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], $count);
        $this->assertEquals($result['result'], [$serializedResult]);
    }
}
