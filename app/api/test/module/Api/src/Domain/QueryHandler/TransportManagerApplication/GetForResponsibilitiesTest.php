<?php

declare(strict_types=1);

/**
 * GetForResponsibilities Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerApplication\GetForResponsibilities as QueryHandler;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetForResponsibilities as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportMangerApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence as OtherLicenceRepo;
use Mockery as m;

/**
 * GetForResponsibilities Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetForResponsibilitiesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TransportManagerApplication', TransportMangerApplicationRepo::class);
        $this->mockRepo('OtherLicence', OtherLicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1]);

        $tma = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $tma->shouldReceive('serialize')->with(
            [
                'application' => [
                    'licence' => [
                        'organisation'
                    ]
                ],
                'operatingCentres',
                'otherLicences' => [
                    'role'
                ]
            ]
        )->once()->andReturn(['foo' => 'bar']);

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('fetchForResponsibilities')
            ->with(1)
            ->once()
            ->andReturn($tma)
            ->getMock();

        $this->assertSame(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }
}
