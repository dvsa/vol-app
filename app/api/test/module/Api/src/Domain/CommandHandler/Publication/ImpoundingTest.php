<?php

declare(strict_types=1);

/**
 * Publish Impounding Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\Impounding;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Publication\Impounding as ImpoundingCmd;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Api\Domain\Repository\Impounding as ImpoundingRepo;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Cases\Impounding as ImpoundingEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData as PoliceEntity;
use Dvsa\Olcs\Api\Service\Publication\PublicationGenerator;
use Dvsa\Olcs\Api\Domain\Command\Result as ResultCmd;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedImpounding as UnpublishedImpoundingQry;

/**
 * Publish Impounding Test
 */
class ImpoundingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Impounding();
        $this->mockRepo('Publication', PublicationRepo::class);
        $this->mockRepo('PublicationLink', PublicationLinkRepo::class);
        $this->mockRepo('Impounding', ImpoundingRepo::class);
        $this->mockRepo('TrafficArea', TrafficAreaRepo::class);

        $this->mockedSmServices = [
            PublicationGenerator::class => m::mock(PublicationGenerator::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'impt_hearing',
            CasesEntity::APP_CASE_TYPE,
            CasesEntity::LICENCE_CASE_TYPE,
            LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
            LicenceEntity::LICENCE_CATEGORY_PSV,
        ];

        $this->references = [
            PublicationSectionEntity::class => [
                PublicationSectionEntity::HEARING_SECTION => m::mock(PublicationSectionEntity::class)
            ],
            ApplicationEntity::class => [
                1 => m::mock(ApplicationEntity::class)->makePartial()
            ],
            TrafficAreaEntity::class => [
                'B' => m::mock(TrafficAreaEntity::class)
                    ->makePartial()
                    ->shouldReceive('getId')
                    ->andReturn('B')
                    ->getMock()
            ],
            LicenceEntity::class => [
                7 => m::mock(LicenceEntity::class)->makePartial()
            ],
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)->makePartial()
            ]
        ];

        parent::initReferences();
    }

    /**
     * Test handle Impounding publication command for case attached to application
     */
    public function testHandleCommandForApplicationCases(): void
    {
        $publicationId = 33;
        $impoundingId = 17;

        $trafficAreaId = 'B';
        $pubTypeId = 'N&P';
        $piId = 44;
        $applicationId = 1;
        $licenceId = null;

        $allTrafficAreas = $this->getAllTrafficAreas();

        $caseType = CasesEntity::APP_CASE_TYPE;

        $command = ImpoundingCmd::Create(
            [
                'trafficArea' => $trafficAreaId,
                'pubType' => $pubTypeId,
                'pi' => $piId,
                'application' => $applicationId,
                'licence' => $licenceId
            ]
        );

        $caseMock = m::mock(CasesEntity::class);
        $caseMock->shouldReceive('getCaseType')->andReturn($this->refData[$caseType]);

        $impoundingMock = m::mock(ImpoundingEntity::class);
        $impoundingMock->shouldReceive('getCase')->andReturn($caseMock);
        $impoundingMock->shouldReceive('getId')->andReturn($impoundingId);

        $publicationMock = $this->getPublicationMock($publicationId);

        $publicationLinkMock = m::mock(PublicationLinkEntity::class)->makePartial();

        $this->references[ApplicationEntity::class][1]->shouldReceive('getLicence')
            ->andReturn($this->references[LicenceEntity::class][7]);

        $this->repoMap['Impounding']->shouldReceive('fetchUsingId')->andReturn($impoundingMock);
        $this->repoMap['TrafficArea']->shouldReceive('fetchAll')->andReturn($allTrafficAreas);
        $this->repoMap['Publication']->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('B', 'A&D')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('B', 'N&P')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('N', 'A&D')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('D', 'A&D')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('D', 'N&P')
            ->once()
            ->andReturn($publicationMock);

        $this->repoMap['PublicationLink']
            ->shouldReceive('fetchSingleUnpublished')
            ->with(m::type(UnpublishedImpoundingQry::class))
            ->andReturn($publicationLinkMock)
            ->shouldReceive('save')
            ->with(m::type(PublicationLinkEntity::class))
            ->shouldReceive('delete')
            ->with(m::type(PublicationLinkEntity::class));

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(ResultCmd::class, $result);
    }

    /**
     * Test handle Impounding publication command for case attached to licence
     */
    public function testHandleCommandForLicenceCases(): void
    {
        $publicationId = 33;
        $impoundingId = 17;

        $trafficAreaId = 'B';
        $pubTypeId = 'N&P';
        $piId = 44;
        $applicationId = null;
        $licenceId = 7;

        $allTrafficAreas = $this->getAllTrafficAreas();

        $caseType = CasesEntity::LICENCE_CASE_TYPE;

        $command = ImpoundingCmd::Create(
            [
                'trafficArea' => $trafficAreaId,
                'pubType' => $pubTypeId,
                'pi' => $piId,
                'application' => $applicationId,
                'licence' => $licenceId
            ]
        );

        $caseMock = m::mock(CasesEntity::class);
        $caseMock->shouldReceive('getCaseType')->andReturn($this->refData[$caseType]);

        $impoundingMock = m::mock(ImpoundingEntity::class);
        $impoundingMock->shouldReceive('getCase')->andReturn($caseMock);
        $impoundingMock->shouldReceive('getId')->andReturn($impoundingId);

        $publicationMock = $this->getPublicationMock($publicationId);

        $publicationLinkMock = m::mock(PublicationLinkEntity::class)->makePartial();

        $this->references[ApplicationEntity::class][1]->shouldReceive('getLicence')
            ->andReturn($this->references[LicenceEntity::class][7]);

        $this->repoMap['Impounding']->shouldReceive('fetchUsingId')->andReturn($impoundingMock);
        $this->repoMap['TrafficArea']->shouldReceive('fetchAll')->andReturn($allTrafficAreas);
        $this->repoMap['Publication']->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('B', 'A&D')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('B', 'N&P')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('N', 'A&D')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('D', 'A&D')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('D', 'N&P')
            ->once()
            ->andReturn($publicationMock);

        $this->repoMap['PublicationLink']
            ->shouldReceive('fetchSingleUnpublished')
            ->with(m::type(UnpublishedImpoundingQry::class))
            ->andReturn($publicationLinkMock)
            ->shouldReceive('save')
            ->with(m::type(PublicationLinkEntity::class))
            ->shouldReceive('delete')
            ->with(m::type(PublicationLinkEntity::class));

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(ResultCmd::class, $result);
    }

    /**
     * Test handle Impounding publication when publication traffic areas have changed
     * @param $cmdClass
     */
    public function testHandleCommandWithDelete(): void
    {
        $publicationId = 33;
        $impoundingId = 17;

        $trafficAreaId = 'B';
        $pubTypeId = 'N&P';
        $piId = 44;
        $applicationId = null;
        $licenceId = 7;

        $allTrafficAreas = $this->getAllTrafficAreas();

        $caseType = CasesEntity::LICENCE_CASE_TYPE;

        $command = ImpoundingCmd::Create(
            [
                'trafficArea' => $trafficAreaId,
                'pubType' => $pubTypeId,
                'pi' => $piId,
                'application' => $applicationId,
                'licence' => $licenceId
            ]
        );

        $caseMock = m::mock(CasesEntity::class);
        $caseMock->shouldReceive('getCaseType')->andReturn($this->refData[$caseType]);

        $impoundingMock = m::mock(ImpoundingEntity::class);
        $impoundingMock->shouldReceive('getCase')->andReturn($caseMock);
        $impoundingMock->shouldReceive('getId')->andReturn($impoundingId);

        $publicationMock = $this->getPublicationMock($publicationId);

        $publicationLinkMock = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLinkMock->shouldReceive('getId')->andReturn(1);
        $publicationLinkMock->shouldReceive('getPoliceDatas->clear')->times(5)->andReturnSelf();

        $this->references[ApplicationEntity::class][1]->shouldReceive('getLicence')
            ->andReturn($this->references[LicenceEntity::class][7]);

        $this->repoMap['Impounding']->shouldReceive('fetchUsingId')->andReturn($impoundingMock);
        $this->repoMap['TrafficArea']->shouldReceive('fetchAll')->andReturn($allTrafficAreas);
        $this->repoMap['Publication']->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('B', 'A&D')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('B', 'N&P')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('N', 'A&D')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('D', 'A&D')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('D', 'N&P')
            ->once()
            ->andReturn($publicationMock);

        $this->repoMap['PublicationLink']
            ->shouldReceive('fetchSingleUnpublished')
            ->with(m::type(UnpublishedImpoundingQry::class))
            ->andReturn($publicationLinkMock)
            ->shouldReceive('delete')
            ->with(m::type(PublicationLinkEntity::class));

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(ResultCmd::class, $result);
    }

    /**
     * Gets a mock publication entity. Assumes canGenerate is always true, as this part is tested on the
     * entity itself
     *
     * @param $publicationId
     * @return m\MockInterface
     */
    private function getPublicationMock(mixed $publicationId): mixed
    {
        $publicationMock = m::mock(PublicationEntity::class);
        $publicationMock->shouldReceive('getId')->andReturn($publicationId);
        $publicationMock->shouldReceive('canGenerate')->andReturn(true);

        return $publicationMock;
    }

    /**
     * Return static list representing all TAs
     * @return array
     */
    private function getAllTrafficAreas(): array
    {
        return [
            0 => m::mock(TrafficAreaEntity::class)->shouldReceive('getId')->andReturn('B')->getMock(),
            1 => m::mock(TrafficAreaEntity::class)->shouldReceive('getId')->andReturn('N')->getMock(),
            2 => m::mock(TrafficAreaEntity::class)->shouldReceive('getId')->andReturn('D')->getMock()
        ];
    }
}
