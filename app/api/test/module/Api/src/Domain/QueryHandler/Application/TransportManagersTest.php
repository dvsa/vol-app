<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\TransportManagers as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Application\TransportManagers as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportManagerApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TransportManagerLicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Mockery as m;

/**
 * TransportManagersTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagersTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('TransportManagerApplication', TransportManagerApplicationRepo::class);
        $this->mockRepo('TransportManagerLicence', TransportManagerLicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $applicationId = 1066;
        $licenceId = 1077;
        $query = Query::create(['id' => $applicationId]);

        $application = m::mock()
            ->shouldReceive('getId')
            ->andReturn($applicationId)
            ->twice()
            ->shouldReceive('getLicenceType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn('licType')
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn($licenceId)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($application);

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('fetchWithContactDetailsByApplication')
            ->with($applicationId)
            ->andReturn('tmas')
            ->once();

        $this->repoMap['TransportManagerLicence']
            ->shouldReceive('fetchWithContactDetailsByLicence')
            ->with($licenceId)
            ->andReturn('tmls')
            ->once();

        $expected = [
            'id' => $applicationId,
            'licenceType' => ['id' => 'licType'],
            'transportManagers' => 'tmas',
            'licence' => [
                'tmLicences' => 'tmls'
            ]
        ];

        $result = $this->sut->handleQuery($query);

        $this->assertEquals($expected, $result);
    }
}
