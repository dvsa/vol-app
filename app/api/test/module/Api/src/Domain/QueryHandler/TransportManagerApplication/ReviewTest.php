<?php

declare(strict_types=1);

/**
 * Review Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerApplication\Review as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as Repo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Generator;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\Review as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Review Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReviewTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TransportManagerApplication', Repo::class);

        $this->mockedSmServices['TmReviewSnapshot'] = m::mock(Generator::class);
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class] =
            m::mock(\LmcRbacMvc\Service\AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 111]);

        $tma = m::mock(TransportManagerApplication::class);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($tma);

        $this->mockedSmServices['TmReviewSnapshot']->shouldReceive('generate')
            ->once()
            ->with($tma, true)
            ->andReturn('<markup>');

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]->shouldReceive('isGranted')
            ->with(\Dvsa\Olcs\Api\Entity\User\Permission::INTERNAL_USER, null)->once()->andReturn(true);

        $this->assertEquals(['markup' => '<markup>'], $this->sut->handleQuery($query));
    }
}
