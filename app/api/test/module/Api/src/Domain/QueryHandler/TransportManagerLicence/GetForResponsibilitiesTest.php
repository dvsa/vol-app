<?php

declare(strict_types=1);

/**
 * GetForResponsibilities Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TransportManagerLicence;

use Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerLicence\GetForResponsibilities as QueryHandler;
use Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetForResponsibilities as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TransportMangerLicenceRepo;
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
        $this->mockRepo('TransportManagerLicence', TransportMangerLicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1]);

        $tma = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $tma->shouldReceive('serialize')->with(
            [
                'otherLicences' => [
                    'role'
                ],
                'licence',
                'operatingCentres'
            ]
        )->once()->andReturn(['foo' => 'bar']);

        $this->repoMap['TransportManagerLicence']
            ->shouldReceive('fetchForResponsibilities')
            ->with(1)
            ->once()
            ->andReturn($tma)
            ->getMock();

        $this->assertSame(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }
}
