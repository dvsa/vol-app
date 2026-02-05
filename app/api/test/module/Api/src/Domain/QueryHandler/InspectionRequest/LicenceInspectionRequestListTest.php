<?php

declare(strict_types=1);

/**
 * Licence Inspection Request Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\QueryHandler\InspectionRequest\LicenceInspectionRequestList as QueryHandler;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\LicenceInspectionRequestList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Mockery as m;
use Doctrine\ORM\Query as OrmQuery;

/**
 * Licence Inspection Request List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceInspectionRequestListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('InspectionRequest', InspectionRequestRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['licence' => 1]);

        $this->repoMap['InspectionRequest']
            ->shouldReceive('fetchList')
            ->with($query, OrmQuery::HYDRATE_OBJECT)
            ->andReturn(
                [
                    m::mock(BundleSerializableInterface::class)
                        ->shouldReceive('serialize')
                        ->andReturn(['foo'])
                        ->once()
                        ->getMock()
                ]
            )
            ->once()
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(1)
            ->once()
            ->getMock();

        $this->assertSame(['result' => [['foo']], 'count' => 1], $this->sut->handleQuery($query));
    }
}
