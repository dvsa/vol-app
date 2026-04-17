<?php

declare(strict_types=1);

/**
 * Application Inspection Request Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\InspectionRequest;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\InspectionRequest\ApplicationInspectionRequestList as QueryHandler;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\ApplicationInspectionRequestList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;

/**
 * Application Inspection Request List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationInspectionRequestListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('InspectionRequest', InspectionRequestRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['application' => 1]);

        $mockLicence = m::mock()
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

        $this->repoMap['Application']
            ->shouldReceive('fetchWithLicence')
            ->with(1)
            ->once()
            ->andReturn($mockLicence);

        $inspectionRequests = new ArrayCollection();
        $inspectionRequest = m::mock()
            ->shouldReceive('serialize')
            ->andReturn(['foo'])
            ->once()
            ->getMock();

        $inspectionRequests->add($inspectionRequest);

        $this->repoMap['InspectionRequest']
            ->shouldReceive('fetchPage')
            ->with($query, 2)
            ->andReturn(['result' => $inspectionRequests, 'count' => 1])
            ->once()
            ->getMock();

        $this->assertSame(['result' => [['foo']], 'count' => 1], $this->sut->handleQuery($query));
    }
}
