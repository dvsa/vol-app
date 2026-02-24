<?php

declare(strict_types=1);

/**
 * LicenceDecisionsTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\LicenceDecisions;
use Dvsa\Olcs\Api\Domain\Repository\BusRegSearchView;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceDecisions as Qry;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence as GetListByLicenceQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Licence Decisions Test
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class LicenceDecisionsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new LicenceDecisions();
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('BusRegSearchView', BusRegSearchView::class);

        parent::setUp();
    }

    public function testHandleQueryDecisionsTrue(): void
    {
        $licenceId = 1;

        $query = Qry::create(['id' => $licenceId]);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getActiveCommunityLicences')->andReturn(new ArrayCollection(['one']));
        $licence->shouldReceive('getActiveVariations')->andReturn(new ArrayCollection(['one']));
        $licence->shouldReceive('getOngoingIrhpApplications')->withNoArgs()->andReturn(new ArrayCollection(['one']));
        $licence->shouldReceive('hasValidCertificateOfRoadworthinessApplications')->withNoArgs()->andReturnTrue();
        $licence->shouldReceive('serialize')->andReturn(['foo' => 'bar']);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchCount')
            ->with(m::type(GetListByLicenceQuery::class))
            ->andReturnUsing(function ($query) use (
                $licenceId
            ) {
                $this->assertEquals($licenceId, $query->getLicence());
                $this->assertTrue($query->getValidOnly());

                return 2;
            });

        $this->repoMap['BusRegSearchView']->shouldReceive('fetchActiveByLicence')->with($licence)->once()
            ->andReturn(new ArrayCollection(['one']));

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(
            [
                'foo' => 'bar',
                'suitableForDecisions' => [
                    'activeComLics' => true,
                    'activeBusRoutes' => true,
                    'activeVariations' => true,
                    'activePermits' => true,
                    'ongoingPermitApplications' => true,
                    'validCorPermitApplications' => true,
                ]
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryDecisionsFalse(): void
    {
        $licenceId = 1;

        $query = Qry::create(['id' => $licenceId]);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getActiveCommunityLicences')->andReturn(new ArrayCollection([]));
        $licence->shouldReceive('getActiveVariations')->andReturn(new ArrayCollection([]));
        $licence->shouldReceive('getOngoingIrhpApplications')->withNoArgs()->andReturn(new ArrayCollection([]));
        $licence->shouldReceive('hasValidCertificateOfRoadworthinessApplications')->withNoArgs()->andReturnFalse();
        $licence->shouldReceive('serialize')->andReturn(['foo' => 'bar']);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchCount')
            ->with(m::type(GetListByLicenceQuery::class))
            ->andReturnUsing(function ($query) use (
                $licenceId
            ) {
                $this->assertEquals($licenceId, $query->getLicence());
                $this->assertTrue($query->getValidOnly());

                return 0;
            });

        $this->repoMap['BusRegSearchView']->shouldReceive('fetchActiveByLicence')->with($licence)->once()
            ->andReturn(new ArrayCollection([]));

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(
            [
                'foo' => 'bar',
                'suitableForDecisions' => true
            ],
            $result->serialize()
        );
    }
}
