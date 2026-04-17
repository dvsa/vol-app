<?php

declare(strict_types=1);

/** PiHearingTest
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\Application;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Api\Domain\Repository\PiHearing as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Publication\Application as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Service\Publication\PublicationGenerator;
use Dvsa\Olcs\Api\Domain\Command\Result as ResultCmd;

/**
 * ApplicationTest
 */
class ApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Application();
        $this->mockRepo('Publication', PublicationRepo::class);
        $this->mockRepo('PublicationLink', PublicationLinkRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('TrafficArea', TrafficAreaRepo::class);

        $this->mockedSmServices = [
            PublicationGenerator::class => m::mock(PublicationGenerator::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            TrafficAreaEntity::class => [
                'M' => m::mock(TrafficAreaEntity::class)
            ],
            PublicationSectionEntity::class => [
                PublicationSectionEntity::APP_NEW_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::APP_GRANTED_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::APP_REFUSED_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::APP_WITHDRAWN_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::APP_GRANT_NOT_TAKEN_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::VAR_GRANTED_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::SCHEDULE_4_TRUE => m::mock(PublicationSectionEntity::class),
            ]
        ];

        parent::initReferences();
    }

    /**
     * testHandleCommand
     *
     * @param String $appStatus
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $appStatus): void
    {
        $id = 99;
        $licenceId = 88;
        $licType = LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE;
        $trafficArea = 'M';
        $publicationId = 33;

        $command = Cmd::Create(
            [
                'id' => $id,
                'trafficArea' => $trafficArea
            ]
        );

        $publicationLink = new PublicationLinkEntity();

        $this->mockedSmServices[PublicationGenerator::class]
            ->shouldReceive('createPublication')->with('ApplicationPublication', $publicationLink, [])->once()
            ->andReturn($publicationLink);

        $publicationMock = $this->getPublicationMock($publicationId);

        $mockTa = m::mock(TrafficAreaEntity::class);
        $mockTa->shouldReceive('getId')->andReturn($trafficArea);

        $licenceMock = m::mock(LicenceEntity::class);
        $licenceMock->shouldReceive('getId')->andReturn($licenceId);

        $applicationMock = m::mock(ApplicationEntity::class);
        $applicationMock->shouldReceive('getLicence')->andReturn($licenceMock);
        $applicationMock->shouldReceive('getLicence->getId')->andReturn($licenceId);
        $applicationMock->shouldReceive('getStatus->getId')->andReturn($appStatus);
        $applicationMock->shouldReceive('getGoodsOrPsv->getId')->andReturn($licType);
        $applicationMock->shouldReceive('getId')->andReturn($id);
        $applicationMock->shouldReceive('isNew')->andReturn(true);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->andReturn($applicationMock);

        $this->repoMap['Publication']->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->andReturn($publicationMock);

        $this->repoMap['PublicationLink']->shouldReceive('fetchSingleUnpublished')
            ->andReturn($publicationLink)
            ->shouldReceive('save')
            ->with(m::type(PublicationLinkEntity::class));

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(ResultCmd::class, $result);
    }

    public function testHandleCommandVariation(): void
    {
        $id = 99;
        $licenceId = 88;
        $licType = LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE;
        $trafficArea = 'M';
        $publicationId = 33;

        $command = Cmd::Create(
            [
                'id' => $id,
                'trafficArea' => $trafficArea,
                'publicationSection' => PublicationSectionEntity::VAR_GRANTED_SECTION,
            ]
        );

        $publicationLink = new PublicationLinkEntity();

        $this->mockedSmServices[PublicationGenerator::class]
            ->shouldReceive('createPublication')->with('VariationPublication', $publicationLink, [])->once()
            ->andReturn($publicationLink);

        $publicationMock = $this->getPublicationMock($publicationId);

        $mockTa = m::mock(TrafficAreaEntity::class);
        $mockTa->shouldReceive('getId')->andReturn($trafficArea);

        $licenceMock = m::mock(LicenceEntity::class);
        $licenceMock->shouldReceive('getId')->andReturn($licenceId);

        $applicationMock = m::mock(ApplicationEntity::class);
        $applicationMock->shouldReceive('getLicence')->andReturn($licenceMock);
        $applicationMock->shouldReceive('getLicence->getId')->andReturn($licenceId);
        $applicationMock->shouldReceive('getStatus->getId')->andReturn('foo');
        $applicationMock->shouldReceive('getGoodsOrPsv->getId')->andReturn($licType);
        $applicationMock->shouldReceive('getId')->andReturn($id);
        $applicationMock->shouldReceive('isNew')->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->andReturn($applicationMock);

        $this->repoMap['Publication']->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->andReturn($publicationMock);

        $this->repoMap['PublicationLink']->shouldReceive('fetchSingleUnpublished')
            ->andReturn($publicationLink)
            ->shouldReceive('save')
            ->with(m::type(PublicationLinkEntity::class));

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(ResultCmd::class, $result);
    }

    public function testHandleCommandTrueSchedue41(): void
    {
        $id = 99;
        $licenceId = 88;
        $licType = LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE;
        $trafficArea = 'M';
        $publicationId = 33;

        $command = Cmd::Create(
            [
                'id' => $id,
                'trafficArea' => $trafficArea,
                'publicationSection' => PublicationSectionEntity::SCHEDULE_4_TRUE,
            ]
        );

        $publicationLink = new PublicationLinkEntity();

        $this->mockedSmServices[PublicationGenerator::class]
            ->shouldReceive('createPublication')->with('Schedule41TruePublication', $publicationLink, [])->once()
            ->andReturn($publicationLink);

        $publicationMock = $this->getPublicationMock($publicationId);

        $mockTa = m::mock(TrafficAreaEntity::class);
        $mockTa->shouldReceive('getId')->andReturn($trafficArea);

        $licenceMock = m::mock(LicenceEntity::class);
        $licenceMock->shouldReceive('getId')->andReturn($licenceId);

        $applicationMock = m::mock(ApplicationEntity::class);
        $applicationMock->shouldReceive('getLicence')->andReturn($licenceMock);
        $applicationMock->shouldReceive('getLicence->getId')->andReturn($licenceId);
        $applicationMock->shouldReceive('getStatus->getId')->andReturn('foo');
        $applicationMock->shouldReceive('getGoodsOrPsv->getId')->andReturn($licType);
        $applicationMock->shouldReceive('getId')->andReturn($id);
        $applicationMock->shouldReceive('isNew')->andReturn(false);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($applicationMock, $licenceMock);
        $s4->setIsTrueS4('Y');
        $applicationMock->shouldReceive('getS4s')->andReturn(new \Doctrine\Common\Collections\ArrayCollection([$s4]));

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->andReturn($applicationMock);

        $this->repoMap['Publication']->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->andReturn($publicationMock);

        $this->repoMap['PublicationLink']->shouldReceive('fetchSingleUnpublished')
            ->andReturn($publicationLink)
            ->shouldReceive('save')
            ->with(m::type(PublicationLinkEntity::class));

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(ResultCmd::class, $result);
    }

    public function testHandleCommandUnTrueSchedue41(): void
    {
        $id = 99;
        $licenceId = 88;
        $licType = LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE;
        $trafficArea = 'M';
        $publicationId = 33;

        $command = Cmd::Create(
            [
                'id' => $id,
                'trafficArea' => $trafficArea,
                'publicationSection' => PublicationSectionEntity::SCHEDULE_4_TRUE,
            ]
        );

        $publicationLink = new PublicationLinkEntity();

        $this->mockedSmServices[PublicationGenerator::class]
            ->shouldReceive('createPublication')->with('Schedule41UntruePublication', $publicationLink, [])->once()
            ->andReturn($publicationLink);

        $publicationMock = $this->getPublicationMock($publicationId);

        $mockTa = m::mock(TrafficAreaEntity::class);
        $mockTa->shouldReceive('getId')->andReturn($trafficArea);

        $licenceMock = m::mock(LicenceEntity::class);
        $licenceMock->shouldReceive('getId')->andReturn($licenceId);

        $applicationMock = m::mock(ApplicationEntity::class);
        $applicationMock->shouldReceive('getLicence')->andReturn($licenceMock);
        $applicationMock->shouldReceive('getLicence->getId')->andReturn($licenceId);
        $applicationMock->shouldReceive('getStatus->getId')->andReturn('foo');
        $applicationMock->shouldReceive('getGoodsOrPsv->getId')->andReturn($licType);
        $applicationMock->shouldReceive('getId')->andReturn($id);
        $applicationMock->shouldReceive('isNew')->andReturn(false);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($applicationMock, $licenceMock);
        $s4->setIsTrueS4('N');
        $applicationMock->shouldReceive('getS4s')->andReturn(new \Doctrine\Common\Collections\ArrayCollection([$s4]));

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->andReturn($applicationMock);

        $this->repoMap['Publication']->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->andReturn($publicationMock);

        $this->repoMap['PublicationLink']->shouldReceive('fetchSingleUnpublished')
            ->andReturn($publicationLink)
            ->shouldReceive('save')
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
     *
     * @param string $appStatus
     * @param int $expectedSection
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('publicationSectionIdProvider')]
    public function testGetPublicationSectionId(mixed $appStatus, mixed $isVariation, mixed $expectedSection): void
    {
        $application = $this->getTestingApplication();
        $application->setIsVariation($isVariation);
        $application->getStatus()->setId($appStatus);

        $this->assertEquals($expectedSection, $this->sut->getPublicationSectionId($application));
    }

    public function testInvalidSectionIdException(): void
    {
        $this->expectException(\RuntimeException::class);

        $application = $this->getTestingApplication();
        $application->getStatus()->setId('some_status');

        $this->sut->getPublicationSectionId($application);
    }

    /**
     * Data provider for handleCommand
     *
     * @return array
     */
    public static function handleCommandProvider(): array
    {
        return [
            [ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION],
            [ApplicationEntity::APPLICATION_STATUS_GRANTED],
            [ApplicationEntity::APPLICATION_STATUS_REFUSED],
            [ApplicationEntity::APPLICATION_STATUS_WITHDRAWN],
            [ApplicationEntity::APPLICATION_STATUS_NOT_TAKEN_UP],
        ];
    }

    /**
     * Data provider for testGetPublicationSectionId
     *
     * @return array
     */
    public static function publicationSectionIdProvider(): array
    {
        return [
            [
                ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION,
                false,
                PublicationSectionEntity::APP_NEW_SECTION
            ],
            [ApplicationEntity::APPLICATION_STATUS_GRANTED, false, PublicationSectionEntity::APP_GRANTED_SECTION],
            [ApplicationEntity::APPLICATION_STATUS_REFUSED, false, PublicationSectionEntity::APP_REFUSED_SECTION],
            [ApplicationEntity::APPLICATION_STATUS_WITHDRAWN, false, PublicationSectionEntity::APP_WITHDRAWN_SECTION],
            [
                ApplicationEntity::APPLICATION_STATUS_NOT_TAKEN_UP,
                false,
                PublicationSectionEntity::APP_GRANT_NOT_TAKEN_SECTION
            ],
            [
                ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION,
                true,
                PublicationSectionEntity::VAR_NEW_SECTION
            ],
            [ApplicationEntity::APPLICATION_STATUS_GRANTED, true, PublicationSectionEntity::VAR_GRANTED_SECTION],
            [ApplicationEntity::APPLICATION_STATUS_REFUSED, true, PublicationSectionEntity::VAR_REFUSED_SECTION],
            [ApplicationEntity::APPLICATION_STATUS_WITHDRAWN, true, PublicationSectionEntity::APP_WITHDRAWN_SECTION],
        ];
    }
}
