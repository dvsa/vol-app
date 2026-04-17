<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Publication;

use Dvsa\Olcs\Api\Domain\QueryHandler\Publication\PendingList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Transfer\Query\Publication\PendingList as Qry;
use Mockery as m;

class PendingListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PendingList();
        $this->mockRepo('Publication', PublicationRepo::class);

        parent::setUp();
    }

    /**
     * tests retrieving a list of pending publications (status new or generated)
     */
    public function testHandleQuery(): void
    {
        $count = 25;
        $query = Qry::create([]);
        $serializedResult = 'foo';

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn($serializedResult);

        $queryResult = [
            'results' => [0 => $mockResult],
            'count' => $count
        ];

        $this->repoMap['Publication']->shouldReceive('fetchPendingList')
            ->andReturn($queryResult);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], $count);
        $this->assertEquals($result['result'], [$serializedResult]);
    }
}
