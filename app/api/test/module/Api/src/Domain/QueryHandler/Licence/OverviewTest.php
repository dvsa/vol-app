<?php

declare(strict_types=1);

/**
 * Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\Overview;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Transfer\Query\Licence\Overview as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\BusRegSearchView as BusRegSearchViewRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class OverviewTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Overview();

        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('TrafficArea', TrafficAreaRepo::class);
        $this->mockRepo('BusRegSearchView', BusRegSearchViewRepository::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $licenceId = 7;
        $organisationId = 1;
        $applicableAuthProperties = [
            'totAuthVehicles',
            'totAuthTrailers',
        ];

        $query = Qry::create(['id' => $licenceId]);

        $cases = [
            $this->getMockCase(1),
            $this->getMockCase(2),
        ];
        $mockLicence = m::mock(LicenceEntity::class)->makePartial();
        $mockLicence
            ->setId($licenceId)
            ->shouldReceive('serialize')
            ->andReturn(['id' => $licenceId])
            ->shouldReceive('getOpenCases')
            ->andReturn($cases)
            ->shouldReceive('getTradingName')
            ->andReturn('Foo plc')
            ->shouldReceive('getOpenComplaintsCount')
            ->andReturn(5)
            ->shouldReceive('getActiveVehicles')
            ->andReturn(new ArrayCollection([1, 2]))
            ->once()
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                ->shouldReceive('getActiveLicences')
                ->andReturn(new ArrayCollection([1, 2, 3]))
                ->once()
                ->shouldReceive('getId')
                ->andReturn($organisationId)
                ->once()
                ->getMock()
            )
            ->shouldReceive('getFirstApplicationId')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->once()
            ->andReturn($applicableAuthProperties)
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLicence);

        $this->repoMap['BusRegSearchView']
            ->shouldReceive('fetchCount')
            ->andReturn('4');

        $this->repoMap['Application']
            ->shouldReceive('fetchActiveForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn(
                [
                    $this->getMockApplication(1),
                    $this->getMockApplication(2),
                    $this->getMockApplication(3),
                ]
            );

        $this->repoMap['TrafficArea']
            ->shouldReceive('getValueOptions')
            ->once()
            ->andReturn(
                [
                    'A' => 'Area A',
                    'B' => 'Area B',
                ]
            );

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $this->assertEquals(
            [
                'id' => 7,
                'currentApplications' => [
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3],
                ],
                'openCases' => [
                    ['id' => 1],
                    ['id' => 2],
                ],
                'valueOptions' => [
                    'trafficAreas' => [
                        'A' => 'Area A',
                        'B' => 'Area B',
                    ],
                ],
                'tradingName' => 'Foo plc',
                'complaintsCount' => 5,
                'busCount' => '4',
                'organisationLicenceCount' => 3,
                'numberOfVehicles' => 2,
                'firstApplicationId' => 1,
                'applicableAuthProperties' => $applicableAuthProperties,
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryBundleKeepsPsvDiscsNotCeasedWhenTheDiscListIsLoaded(): void
    {
        $query = Qry::create(['id' => 7]);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(7);

        $current = new PsvDisc($licence);
        $ceased = new PsvDisc($licence);
        $ceased->setCeasedDate('2026-01-02T03:04:05+0000');

        $kept = null;
        $licence->shouldReceive('serialize')
            ->once()
            ->andReturnUsing(
                function (array $bundle) use ($current, $ceased, &$kept) {
                    $kept = (new ArrayCollection([$ceased, $current]))
                        ->matching($bundle['psvDiscs']['criteria'])
                        ->getValues();

                    return ['id' => 7];
                }
            );
        $licence->shouldReceive('getOpenCases')->andReturn([]);
        $licence->shouldReceive('getTradingName')->andReturn('Foo plc');
        $licence->shouldReceive('getOpenComplaintsCount')->andReturn(0);
        $licence->shouldReceive('getActiveVehicles')->andReturn(new ArrayCollection());
        $licence->shouldReceive('getOrganisation->getActiveLicences')->andReturn(new ArrayCollection());
        $licence->shouldReceive('getOrganisation->getId')->andReturn(1);
        $licence->shouldReceive('getFirstApplicationId')->andReturn(1);
        $licence->shouldReceive('getApplicableAuthProperties')->andReturn([]);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($query)->andReturn($licence);
        $this->repoMap['BusRegSearchView']->shouldReceive('fetchCount')->andReturn(0);
        $this->repoMap['Application']->shouldReceive('fetchActiveForOrganisation')->with(1)->andReturn([]);
        $this->repoMap['TrafficArea']->shouldReceive('getValueOptions')->andReturn([]);

        $this->sut->handleQuery($query)->serialize();

        $this->assertSame([$current], $kept);
    }

    protected function getMockApplication(mixed $id): m\MockInterface
    {
        return m::mock(ApplicationEntity::class)
            ->makePartial()
            ->setId($id)
            ->shouldReceive('serialize')
            ->andReturn(['id' => $id])
            ->getMock();
    }

    protected function getMockCase(mixed $id): m\MockInterface
    {
        return m::mock(CaseEntity::class)
            ->makePartial()
            ->setId($id)
            ->shouldReceive('serialize')
            ->andReturn(['id' => $id])
            ->getMock();
    }
}
