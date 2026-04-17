<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Cases;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\Note as NoteRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Cases test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CasesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Cases();
        $this->mockRepo('Cases', CasesRepo::class);
        $this->mockRepo('Note', NoteRepo::class);

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->shouldReceive('isAnonymous')->andReturn(false);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
                ->shouldReceive('isGranted')->andReturn(false)->getMock(),
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        parent::setUp();
    }

    public function testHandleQueryLicenceCaseType(): void
    {
        $query = Qry::create(['id' => 24]);
        $licenceId = 77;
        $tmId = 2;
        $latestNote = 'test note';
        $caseType = CasesEntity::LICENCE_CASE_TYPE;

        $mockLicence = m::mock(LicenceEntity::class);
        $mockLicence->shouldReceive('getId')->andReturn($licenceId);

        $mockTransportManager = m::mock(TransportManagerEntity::class);
        $mockTransportManager->shouldReceive('getId')->andReturn($tmId);

        $mockCase = m::mock(CasesEntity::class);
        $mockCase->shouldReceive('serialize')->andReturn(['SERIALIZED']);
        $mockCase->shouldReceive('getCaseType')->andReturn($caseType);
        $mockCase->shouldReceive('getLicence')->andReturn($mockLicence);
        $mockCase->shouldReceive('getTransportManager')->andReturn($mockTransportManager);

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockCase);
        $this->repoMap['Note']->shouldReceive('fetchForOverview')
            ->with($licenceId)->andReturn($latestNote);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(
            [
                'SERIALIZED',
                'latestNote' => 'test note'
            ],
            $result->serialize()
        );
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleQueryApplicationCaseType(): void
    {
        $query = Qry::create(['id' => 24]);
        $licenceId = 77;
        $applicationId = 4;
        $tmId = 2;
        $latestNote = 'test note';
        $caseType = CasesEntity::APP_CASE_TYPE;

        $mockLicence = m::mock(LicenceEntity::class);
        $mockLicence->shouldReceive('getId')->andReturn($licenceId);

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class);
        $mockApplication->shouldReceive('getId')->andReturn($applicationId);
        $mockApplication->shouldReceive('getLicence')->andReturn($mockLicence);

        $mockTransportManager = m::mock(TransportManagerEntity::class);
        $mockTransportManager->shouldReceive('getId')->andReturn($tmId);

        $mockCase = m::mock(CasesEntity::class);
        $mockCase->shouldReceive('serialize')->andReturn(['SERIALIZED']);
        $mockCase->shouldReceive('getCaseType')->andReturn($caseType);
        $mockCase->shouldReceive('getApplication')
            ->andReturn($mockApplication);
        $mockCase->shouldReceive('getTransportManager')->andReturn($mockTransportManager);

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockCase);
        $this->repoMap['Note']->shouldReceive('fetchForOverview')
            ->with($licenceId)->andReturn($latestNote);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(
            [
                'SERIALIZED',
                'latestNote' => 'test note'
            ],
            $result->serialize()
        );
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleQueryTmCaseType(): void
    {
        $query = Qry::create(['id' => 24]);
        $tmId = 2;
        $latestNote = 'test note';
        $caseType = CasesEntity::TM_CASE_TYPE;

        $mockTransportManager = m::mock(TransportManagerEntity::class);
        $mockTransportManager->shouldReceive('getId')->andReturn($tmId);

        $mockCase = m::mock(CasesEntity::class);
        $mockCase->shouldReceive('serialize')->andReturn(['SERIALIZED']);
        $mockCase->shouldReceive('getCaseType')->andReturn($caseType);
        $mockCase->shouldReceive('getTransportManager')->andReturn($mockTransportManager);

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockCase);
        $this->repoMap['Note']->shouldReceive('fetchForOverview')
            ->with(null, null, $tmId)->andReturn($latestNote);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(
            [
                'SERIALIZED',
                'latestNote' => 'test note'
            ],
            $result->serialize()
        );
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleQueryOtherCaseType(): void
    {
        $query = Qry::create(['id' => 24]);
        $latestNote = 'test note';
        $caseType = 'some-other';

        $mockCase = m::mock(CasesEntity::class);
        $mockCase->shouldReceive('serialize')->andReturn(['SERIALIZED']);
        $mockCase->shouldReceive('getCaseType')->andReturn($caseType);

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockCase);
        $this->repoMap['Note']->shouldReceive('fetchForOverview')
            ->with(null, null, null, m::type('string'))->andReturn($latestNote);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(
            [
                'SERIALIZED',
                'latestNote' => 'test note'
            ],
            $result->serialize()
        );
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleQueryFilterPublicationLinks(): void
    {
        $query = Qry::create(['id' => 24]);
        $latestNote = 'test note';
        $caseType = 'some-other';

        $this->repoMap['Note']->shouldReceive('fetchForOverview')
            ->with(null, null, null, m::type('string'))->andReturn($latestNote);

        $mockApplication = m::mock(ApplicationEntity::class)->makePartial();
        $mockApplication->initCollections();

        $publicationSectionAppNew = new PublicationSectionEntity();
        $publicationSectionAppNew->setId(PublicationSectionEntity::APP_NEW_SECTION);

        $publicationLink1Id = 100;
        $publicationLink1 = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLink1->setId($publicationLink1Id);
        $publicationLink1->setPublicationSection($publicationSectionAppNew);
        $mockApplication->getPublicationLinks()->add($publicationLink1);

        $publicationSectionVarNew = new PublicationSectionEntity();
        $publicationSectionVarNew->setId(PublicationSectionEntity::VAR_NEW_SECTION);

        $publicationLink2Id = 200;
        $publicationLink2 = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLink2->setId($publicationLink2Id);
        $publicationLink2->setPublicationSection($publicationSectionVarNew);
        $mockApplication->getPublicationLinks()->add($publicationLink2);

        $publicationSectionBusNew = new PublicationSectionEntity();
        $publicationSectionBusNew->setId(PublicationSectionEntity::BUS_NEW_SECTION);

        $publicationLink3Id = 300;
        $publicationLink3 = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLink3->setId($publicationLink3Id);
        $publicationLink3->setPublicationSection($publicationSectionBusNew);
        $mockApplication->getPublicationLinks()->add($publicationLink3);

        $mockCase = m::mock(CasesEntity::class)->makePartial();
        $mockCase->initCollections();
        $mockCase->setApplication($mockApplication);
        $mockCase->shouldReceive('getCaseType')->andReturn($caseType);

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockCase);

        $result = $this->sut->handleQuery($query);

        $serialized = $result->serialize();

        $this->assertSame($latestNote, $serialized['latestNote']);
        $this->assertSame(2, count($serialized['application']['publicationLinks']));
        $this->assertSame($publicationLink1Id, $serialized['application']['publicationLinks'][0]['id']);
        $this->assertSame($publicationLink2Id, $serialized['application']['publicationLinks'][1]['id']);
    }
}
