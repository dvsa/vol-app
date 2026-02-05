<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerApplication\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as Repo;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * GetListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TransportManagerApplication', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create([]);

        $mockTma = m::mock();

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)->once()->andReturn([$mockTma]);
        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchCount')->with($query)->once();

        $mockTma->shouldReceive('serialize')->with(
            [
                'application' => [
                    'status',
                    'licenceType',
                    'licence' => [
                        'organisation'
                    ]
                ],
                'transportManager' => [
                    'homeCd' => [
                        'person',
                    ],
                    'tmType',
                ]
            ]
        )->once()->andReturn('RESULT');

        $this->sut->handleQuery($query);
    }
}
