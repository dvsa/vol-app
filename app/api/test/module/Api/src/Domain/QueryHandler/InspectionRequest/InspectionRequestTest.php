<?php

declare(strict_types=1);

/**
 * Inspection Request Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\QueryHandler\InspectionRequest\InspectionRequest as QueryHandler;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\InspectionRequest as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;

/**
 * Inspection Request Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequestTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('InspectionRequest', InspectionRequestRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $bundle = [
            'reportType',
            'requestType',
            'resultType',
            'application' => [
                'licenceType',
                'operatingCentres' => [
                    'operatingCentre' => [
                        'address'
                    ],
                ],
                'licence' => [
                    'enforcementArea'
                ]
            ],
            'licence' => [
                'enforcementArea',
                'licenceType',
                'organisation' => [
                    'tradingNames',
                    'licences',
                ],
                'operatingCentres',
                'correspondenceCd' => [
                    'address' => [],
                    'phoneContacts' => [
                        'phoneContactType',
                    ],
                ],
                'tmLicences' => [
                    'transportManager' => [
                        'homeCd' => [
                            'person',
                        ],
                    ],
                ],
            ],
            'operatingCentre' => [
                'address'
            ],
        ];
        $query = Query::create(['id' => 1]);

        $mock = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')->with($bundle)
            ->once()
            ->andReturn(['foo'])
            ->getMock();

        $this->repoMap['InspectionRequest']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mock);

        $this->assertSame(['foo'], $this->sut->handleQuery($query)->serialize());
    }
}
