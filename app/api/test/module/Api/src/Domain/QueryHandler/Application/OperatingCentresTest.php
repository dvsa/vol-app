<?php

declare(strict_types=1);

/**
 * Operating Centres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Service\VariationOperatingCentreHelper;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficAreaEnforcementArea;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\Role;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\OperatingCentres as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Application\OperatingCentres as Qry;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Operating Centres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('ApplicationOperatingCentre', Repository\ApplicationOperatingCentre::class);
        $this->mockRepo('TrafficArea', Repository\TrafficArea::class);
        $this->mockRepo('Document', Repository\Document::class);

        $this->mockedSmServices['VariationOperatingCentreHelper'] = m::mock(VariationOperatingCentreHelper::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleQueryNewApp(): void
    {
        $query = Qry::create(['id' => 111]);

        $bundle = [
            'licence' => [
                'trafficArea',
                'enforcementArea'
            ]
        ];

        /** @var Licence $licence */
        $licence = $this->makeMockLicence();

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('serialize')->with($bundle)->andReturn(['foo' => 'bar']);
        $application->shouldReceive('isPsv')->andReturn(false);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->setIsVariation(false);
        $application->setId(111);
        $application->setTotCommunityLicences(12);
        $application->setLicence($licence);
        $application->setNiFlag('N');

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchByApplicationIdForOperatingCentres')
            ->with(111, $query)
            ->andReturn(['a', 'b']);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(true)->getMock();
        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn(new ArrayCollection([]))->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->once()->andReturn($mockedId);

        $this->repoMap['TrafficArea']->shouldReceive('getValueOptions')
            ->with('DUMMY_ALLOWED_OPERATOR_LOCATION')
            ->andReturn(['foo' => 'bar']);

        $documents = [];

        $this->repoMap['Document']->shouldReceive('fetchUnlinkedOcDocumentsForEntity')
            ->with($application)
            ->andReturn($documents);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'requiresVariation' => false,
            'operatingCentres' => ['a', 'b'],
            'totCommunityLicences' => 12,
            'isPsv' => false,
            'canHaveCommunityLicences' => true,
            'canHaveSchedule41' => false,
            'possibleEnforcementAreas' => [],
            'possibleTrafficAreas' => ['foo' => 'bar'],
            'canAddAnother' => false,
            'documents' => []
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    public function testHandleQueryVariationWithTaPsv(): void
    {
        $query = Qry::create(['id' => 111]);

        $bundle = [
            'licence' => [
                'trafficArea',
                'enforcementArea'
            ]
        ];

        /** @var EnforcementArea $ea */
        $ea = m::mock(EnforcementArea::class)->makePartial();
        $ea->setId(33);
        $ea->setName('EA');

        /** @var TrafficAreaEnforcementArea $taea */
        $taea = m::mock(TrafficAreaEnforcementArea::class)->makePartial();
        $taea->setEnforcementArea($ea);

        $taeas = [
            $taea
        ];

        /** @var TrafficArea $ta */
        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setTrafficAreaEnforcementAreas($taeas);
        $licence = $this->makeMockLicence();

        $licence->setTotCommunityLicences(12);
        $licence->setTrafficArea($ta);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('serialize')->with($bundle)->andReturn(['foo' => 'bar']);
        $application->shouldReceive('isPsv')->andReturn(true);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->setIsVariation(true);
        $application->setId(111);
        $application->setLicence($licence);
        $application->setNiFlag('N');

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $this->mockedSmServices['VariationOperatingCentreHelper']
            ->shouldReceive('getListDataForApplication')
            ->with($application, $query)
            ->andReturn(['a', 'b']);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false)->getMock();

        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn(new ArrayCollection([]))->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->once()->andReturn($mockedId);

        $this->repoMap['TrafficArea']->shouldReceive('getValueOptions')
            ->with('DUMMY_ALLOWED_OPERATOR_LOCATION')
            ->andReturn(['foo' => 'bar']);

        $documents = [];

        $this->repoMap['Document']->shouldReceive('fetchUnlinkedOcDocumentsForEntity')
            ->with($application)
            ->andReturn($documents);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'requiresVariation' => false,
            'operatingCentres' => ['a', 'b'],
            'totCommunityLicences' => 12,
            'isPsv' => true,
            'canHaveCommunityLicences' => true,
            'canHaveSchedule41' => false,
            'possibleEnforcementAreas' => [33 => 'EA'],
            'possibleTrafficAreas' => ['foo' => 'bar'],
            'canAddAnother' => true,
            'documents' => []
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    public function testHandleQueryVariationWithTaGoods(): void
    {
        $query = Qry::create(['id' => 111]);

        $bundle = [
            'licence' => [
                'trafficArea',
                'enforcementArea'
            ]
        ];

        /** @var EnforcementArea $ea */
        $ea = m::mock(EnforcementArea::class)->makePartial();
        $ea->setId(33);
        $ea->setName('EA');

        /** @var TrafficAreaEnforcementArea $taea */
        $taea = m::mock(TrafficAreaEnforcementArea::class)->makePartial();
        $taea->setEnforcementArea($ea);

        $taeas = [
            $taea
        ];

        /** @var TrafficArea $ta */
        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setTrafficAreaEnforcementAreas($taeas);

        /** @var Licence $licence */
        $licence = $this->makeMockLicence();
        $licence->setTotCommunityLicences(12);
        $licence->setTrafficArea($ta);

        $status = m::mock(RefData::class)->makePartial();
        $status->setId(Application::APPLICATION_STATUS_GRANTED);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('serialize')->with($bundle)->andReturn(['foo' => 'bar']);
        $application->shouldReceive('isPsv')->andReturn(false);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->setIsVariation(true);
        $application->setId(111);
        $application->setLicence($licence);
        $application->setNiFlag('N');
        $application->setStatus($status);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $this->mockedSmServices['VariationOperatingCentreHelper']
            ->shouldReceive('getListDataForApplication')
            ->with($application, $query)
            ->andReturn(['a', 'b']);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false)->getMock();

        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn(new ArrayCollection([]))->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->once()->andReturn($mockedId);

        $this->repoMap['TrafficArea']->shouldReceive('getValueOptions')
            ->with('DUMMY_ALLOWED_OPERATOR_LOCATION')
            ->andReturn(['foo' => 'bar']);

        $documents = [];

        $this->repoMap['Document']->shouldReceive('fetchUnlinkedOcDocumentsForEntity')
            ->with($application)
            ->andReturn($documents);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'requiresVariation' => false,
            'operatingCentres' => ['a', 'b'],
            'totCommunityLicences' => 12,
            'isPsv' => false,
            'canHaveCommunityLicences' => true,
            'canHaveSchedule41' => false,
            'possibleEnforcementAreas' => [33 => 'EA'],
            'possibleTrafficAreas' => ['foo' => 'bar'],
            'canAddAnother' => true,
            'documents' => []
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    public function testHandleQueryVariationWithTaGoodsUnderConsideration(): void
    {
        $query = Qry::create(['id' => 111]);

        $bundle = [
            'licence' => [
                'trafficArea',
                'enforcementArea'
            ]
        ];

        /** @var EnforcementArea $ea */
        $ea = m::mock(EnforcementArea::class)->makePartial();
        $ea->setId(33);
        $ea->setName('EA');

        /** @var TrafficAreaEnforcementArea $taea */
        $taea = m::mock(TrafficAreaEnforcementArea::class)->makePartial();
        $taea->setEnforcementArea($ea);

        $taeas = [
            $taea
        ];

        /** @var TrafficArea $ta */
        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setTrafficAreaEnforcementAreas($taeas);

        /** @var Licence $licence */
        $licence = $this->makeMockLicence();
        $licence->setTotCommunityLicences(12);
        $licence->setTrafficArea($ta);

        $status = m::mock(RefData::class)->makePartial();
        $status->setId(Application::APPLICATION_STATUS_UNDER_CONSIDERATION);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('serialize')->with($bundle)->andReturn(['foo' => 'bar']);
        $application->shouldReceive('isPsv')->andReturn(false);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->shouldReceive('getActiveS4s')->andReturn(['foo']);
        $application->setIsVariation(true);
        $application->setId(111);
        $application->setLicence($licence);
        $application->setNiFlag('N');
        $application->setStatus($status);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $this->mockedSmServices['VariationOperatingCentreHelper']
            ->shouldReceive('getListDataForApplication')
            ->with($application, $query)
            ->andReturn(['a', 'b']);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false)->getMock();

        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn(new ArrayCollection([]))->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->once()->andReturn($mockedId);

        $this->repoMap['TrafficArea']->shouldReceive('getValueOptions')
            ->with('DUMMY_ALLOWED_OPERATOR_LOCATION')
            ->andReturn(['foo' => 'bar']);

        $documents = [];

        $this->repoMap['Document']->shouldReceive('fetchUnlinkedOcDocumentsForEntity')
            ->with($application)
            ->andReturn($documents);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'requiresVariation' => false,
            'operatingCentres' => ['a', 'b'],
            'totCommunityLicences' => 12,
            'isPsv' => false,
            'canHaveCommunityLicences' => true,
            'canHaveSchedule41' => false,
            'possibleEnforcementAreas' => [33 => 'EA'],
            'possibleTrafficAreas' => ['foo' => 'bar'],
            'canAddAnother' => true,
            'documents' => []
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    public function testHandleQueryVariationWithTaGoodsUnderConsiderationWithoutActiveS4(): void
    {
        $query = Qry::create(['id' => 111]);

        $bundle = [
            'licence' => [
                'trafficArea',
                'enforcementArea'
            ]
        ];

        /** @var EnforcementArea $ea */
        $ea = m::mock(EnforcementArea::class)->makePartial();
        $ea->setId(33);
        $ea->setName('EA');

        /** @var TrafficAreaEnforcementArea $taea */
        $taea = m::mock(TrafficAreaEnforcementArea::class)->makePartial();
        $taea->setEnforcementArea($ea);

        $taeas = [
            $taea
        ];

        /** @var TrafficArea $ta */
        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setTrafficAreaEnforcementAreas($taeas);

        /** @var Licence $licence */
        $licence = $this->makeMockLicence();
        $licence->setTotCommunityLicences(12);
        $licence->setTrafficArea($ta);

        $status = m::mock(RefData::class)->makePartial();
        $status->setId(Application::APPLICATION_STATUS_UNDER_CONSIDERATION);

        /** @var Application $application */
        $application = $this->makeMockApplication($bundle, $licence, $status);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $this->mockedSmServices['VariationOperatingCentreHelper']
            ->shouldReceive('getListDataForApplication')
            ->with($application, $query)
            ->andReturn(['a', 'b']);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false)->getMock();

        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn(new ArrayCollection([]))->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->once()->andReturn($mockedId);

        $this->repoMap['TrafficArea']->shouldReceive('getValueOptions')
            ->with('DUMMY_ALLOWED_OPERATOR_LOCATION')
            ->andReturn(['foo' => 'bar']);

        $documents = [];

        $this->repoMap['Document']->shouldReceive('fetchUnlinkedOcDocumentsForEntity')
            ->with($application)
            ->andReturn($documents);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'requiresVariation' => false,
            'operatingCentres' => ['a', 'b'],
            'totCommunityLicences' => 12,
            'isPsv' => false,
            'canHaveCommunityLicences' => true,
            'canHaveSchedule41' => true,
            'possibleEnforcementAreas' => [33 => 'EA'],
            'possibleTrafficAreas' => ['foo' => 'bar'],
            'canAddAnother' => true,
            'documents' => []
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    public function testHandleQueryReadOnlyUser(): void
    {

        $query = Qry::create(['id' => 111]);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false)->getMock();

        $mockedRole = m::mock(Role::class)->shouldReceive('getRole')->andReturn(Role::ROLE_INTERNAL_LIMITED_READ_ONLY)->getMock();
        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn(new ArrayCollection([$mockedRole]))->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->once()->andReturn($mockedId);
        $bundle = [
            'licence' => [
                'trafficArea',
                'enforcementArea'
            ]
        ];

        /** @var EnforcementArea $ea */
        $ea = m::mock(EnforcementArea::class)->makePartial();
        $ea->setId(33);
        $ea->setName('EA');

        /** @var TrafficAreaEnforcementArea $taea */
        $taea = m::mock(TrafficAreaEnforcementArea::class)->makePartial();
        $taea->setEnforcementArea($ea);

        $taeas = [
            $taea
        ];

        /** @var TrafficArea $ta */
        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setTrafficAreaEnforcementAreas($taeas);

        /** @var Licence $licence */
        $licence = $this->makeMockLicence();
        $licence->setTotCommunityLicences(12);
        $licence->setTrafficArea($ta);

        $status = m::mock(RefData::class)->makePartial();
        $status->setId(Application::APPLICATION_STATUS_UNDER_CONSIDERATION);
        $application = $this->makeMockApplication($bundle, $licence, $status);
        $this->mockedSmServices['VariationOperatingCentreHelper']
            ->shouldReceive('getListDataForApplication')
            ->with($application, $query)
            ->andReturn(['a', 'b']);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $this->repoMap['TrafficArea']->shouldReceive('getValueOptions')
            ->with('DUMMY_ALLOWED_OPERATOR_LOCATION')
            ->andReturn(['foo' => 'bar']);

        $actual = $this->sut->handleQuery($query);
        $results = $actual->serialize();
        $this->assertArrayHasKey('documents', $results);
        $this->assertNull($results['documents']);
    }

    /**
     * @return Licence
     */
    protected function makeMockLicence(): mixed
    {
        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var m\MockInterface|Organisation $organisation */
        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getAllowedOperatorLocation')->andReturn('DUMMY_ALLOWED_OPERATOR_LOCATION');

        $licence->setOrganisation($organisation);

        return $licence;
    }

    /**
     * makeMockApplication
     *
     * @param $bundle
     * @param $licence
     * @param $status
     *
     * @return m\Mock
     */
    protected function makeMockApplication(mixed $bundle, mixed $licence, mixed $status): mixed
    {
        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('serialize')->with($bundle)->andReturn(['foo' => 'bar']);
        $application->shouldReceive('isPsv')->andReturn(false);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->shouldReceive('getActiveS4s')->andReturn([]);
        $application->setIsVariation(true);
        $application->setId(111);
        $application->setLicence($licence);
        $application->setNiFlag('N');
        $application->setStatus($status);
        return $application;
    }
}
