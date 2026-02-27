<?php

declare(strict_types=1);

/**
 * TmResponsibilitiesList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TmResponsibilities;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\TmResponsibilities\TmResponsibilitiesList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportMangerApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TransportMangerLicenceRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\TmResponsibilities\TmResponsibilitiesList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * TmResponsibilitiesList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmResponsibilitiesListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TransportManagerApplication', TransportMangerApplicationRepo::class);
        $this->mockRepo('TransportManagerLicence', TransportMangerLicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(
            [
                'transportManager' => 1
            ]
        );

        $mockLicence = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn('licence')
            ->getMock();

        $mockApplication = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn('application')
            ->getMock();

        $this->repoMap['TransportManagerLicence']
            ->shouldReceive('fetchForTransportManager')
            ->with(
                1,
                [
                    Licence::LICENCE_STATUS_VALID,
                    Licence::LICENCE_STATUS_SUSPENDED,
                    Licence::LICENCE_STATUS_CURTAILED,
                    Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
                ]
            )
            ->once()
            ->andReturn([$mockLicence])
            ->getMock();

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('fetchForTransportManager')
            ->with(
                1,
                [
                    Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
                    Application::APPLICATION_STATUS_NOT_SUBMITTED,
                    Application::APPLICATION_STATUS_GRANTED
                ],
                true
            )
            ->once()
            ->andReturn([$mockApplication])
            ->getMock();

        $this->assertEquals(
            [
                'result' => ['licence'],
                'count'  => 1,
                'tmApplications' => ['application'],
                'tmApplicationsCount' => 1
            ],
            $this->sut->handleQuery($query)
        );
    }
}
