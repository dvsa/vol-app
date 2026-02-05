<?php

declare(strict_types=1);

/**
 * BusTest
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\Bus;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Publication\Bus as Cmd;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Service\Publication\PublicationGenerator;
use Dvsa\Olcs\Api\Domain\Command\Result as ResultCmd;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedBusReg as UnpublishedBusRegQry;

/**
 * BusTest
 */
class BusTest extends AbstractCommandHandlerTestCase
{
    //variables to hold traffic area entity references
    protected $ta1;
    protected $ta2;
    protected $ta3;

    //traffic area id values
    protected $trafficArea = 'M';
    protected $trafficArea2 = 'N'; //NI
    protected $trafficArea3 = 'D';

    public function setUp(): void
    {
        $this->sut = new Bus();
        $this->mockRepo('Publication', PublicationRepo::class);
        $this->mockRepo('PublicationLink', PublicationLinkRepo::class);
        $this->mockRepo('Bus', BusRepo::class);
        $this->mockRepo('TrafficArea', TrafficAreaRepo::class);

        $this->mockedSmServices = [
            PublicationGenerator::class => m::mock(PublicationGenerator::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->ta1 = m::mock(TrafficAreaEntity::class);
        $this->ta1->shouldReceive('getId')->andReturn($this->trafficArea);

        $this->ta2 = m::mock(TrafficAreaEntity::class);
        $this->ta2->shouldReceive('getId')->andReturn($this->trafficArea2);

        $this->ta3 = m::mock(TrafficAreaEntity::class);
        $this->ta3->shouldReceive('getId')->andReturn($this->trafficArea3);

        $this->references = [
            TrafficAreaEntity::class => [
                $this->trafficArea => $this->ta1,
                $this->trafficArea2 => $this->ta2,
                $this->trafficArea3 => $this->ta3
            ],
            PublicationSectionEntity::class => [
                PublicationSectionEntity::BUS_NEW_SHORT_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::BUS_NEW_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::BUS_VAR_SHORT_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::BUS_VAR_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::BUS_CANCEL_SHORT_SECTION => m::mock(PublicationSectionEntity::class),
                PublicationSectionEntity::BUS_CANCEL_SECTION => m::mock(PublicationSectionEntity::class)
            ]
        ];

        parent::initReferences();
    }

    /**
     * testHandleCommand throws exception on incorrect status
     */
    public function testHandleCommandThrowsCorrectException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $id = 99;
        $regNo = 44;
        $revertStatus = 'some_status';
        $shortNotice = 'Y';
        $command = Cmd::Create(['id' => $id]);

        $mockTa = m::mock(TrafficAreaEntity::class);

        $trafficAreas = new ArrayCollection([$mockTa]);

        $licenceMock = m::mock(LicenceEntity::class);

        $busMock = m::mock(BusRegEntity::class);
        $busMock->shouldReceive('getLicence')->andReturn($licenceMock);
        $busMock->shouldReceive('getRevertStatus->getId')->andReturn($revertStatus);
        $busMock->shouldReceive('getTrafficAreas')->andReturn($trafficAreas);
        $busMock->shouldReceive('getRegNo')->andReturn($regNo);
        $busMock->shouldReceive('getIsShortNotice')->andReturn($shortNotice);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')->andReturn($busMock);

        $this->sut->handleCommand($command);
    }

    /**
     * testHandleCommandDelete
     */
    public function testHandleCommandDelete(): void
    {
        $id = 99;
        $publicationId = 33;
        $publicationLinkId = 66;
        $regNo = 44;
        $revertStatus = BusRegEntity::STATUS_NEW;
        $shortNotice = 'Y';

        $allTrafficAreas = [
            0 => $this->ta2,
            1 => $this->ta3
        ];

        $command = Cmd::Create(['id' => $id]);

        $this->repoMap['TrafficArea']->shouldReceive('fetchAll')->andReturn($allTrafficAreas);

        $publicationLinkMock = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLinkMock->shouldReceive('getId')->andReturn($publicationLinkId);
        $publicationLinkMock->shouldReceive('getPoliceDatas->clear')->once()->andReturnSelf();

        $this->mockedSmServices[PublicationGenerator::class]
            ->shouldReceive('createPublication')
            ->andReturn($publicationLinkMock);

        $publicationMock = $this->getPublicationMock($publicationId);

        $mockTa = m::mock(TrafficAreaEntity::class);
        $mockTa->shouldReceive('getId')->andReturn('N');

        $licenceTrafficAreas = new ArrayCollection([$mockTa]);

        $licenceMock = m::mock(LicenceEntity::class);

        $busMock = m::mock(BusRegEntity::class);
        $busMock->shouldReceive('getLicence')->andReturn($licenceMock);
        $busMock->shouldReceive('getRevertStatus->getId')->andReturn($revertStatus);
        $busMock->shouldReceive('getTrafficAreas')->andReturn($licenceTrafficAreas);
        $busMock->shouldReceive('getRegNo')->andReturn($regNo);
        $busMock->shouldReceive('getId')->andReturn($id);
        $busMock->shouldReceive('getIsShortNotice')->andReturn($shortNotice);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')->andReturn($busMock);

        $this->repoMap['Publication']->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('D', 'N&P')
            ->once()
            ->andReturn($publicationMock);

        $this->repoMap['PublicationLink']
            ->shouldReceive('fetchSingleUnpublished')
            ->with(m::type(UnpublishedBusRegQry::class))
            ->andReturn($publicationLinkMock)
            ->shouldReceive('delete')
            ->with(m::type(PublicationLinkEntity::class));

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(ResultCmd::class, $result);
    }

    /**
     * testHandleCommand
     *
     * @param string $revertStatus
     * @param string $shortNotice
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandCreateProvider')]
    public function testHandleCommandCreate(mixed $revertStatus, mixed $shortNotice): void
    {
        $id = 99;
        $publicationId = 33;
        $regNo = 44;

        $allTrafficAreas = [
            0 => $this->ta1,
            1 => $this->ta2,
            2 => $this->ta3
        ];

        $command = Cmd::Create(['id' => $id]);

        $this->repoMap['TrafficArea']->shouldReceive('fetchAll')->andReturn($allTrafficAreas);

        $publicationLinkMock = m::mock(PublicationLinkEntity::class)->makePartial();

        $this->mockedSmServices[PublicationGenerator::class]
            ->shouldReceive('createPublication')
            ->andReturn($publicationLinkMock);

        $publicationMock = $this->getPublicationMock($publicationId);

        $mockTa = m::mock(TrafficAreaEntity::class);
        $mockTa->shouldReceive('getId')->andReturn('M');

        $licenceTrafficAreas = new ArrayCollection([$mockTa]);

        $licenceMock = m::mock(LicenceEntity::class);

        $busMock = m::mock(BusRegEntity::class);
        $busMock->shouldReceive('getLicence')->andReturn($licenceMock);
        $busMock->shouldReceive('getRevertStatus->getId')->andReturn($revertStatus);
        $busMock->shouldReceive('getTrafficAreas')->andReturn($licenceTrafficAreas);
        $busMock->shouldReceive('getRegNo')->andReturn($regNo);
        $busMock->shouldReceive('getId')->andReturn($id);
        $busMock->shouldReceive('getIsShortNotice')->andReturn($shortNotice);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')->andReturn($busMock);

        $this->repoMap['Publication']->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('M', 'N&P')
            ->once()
            ->andReturn($publicationMock)
            ->shouldReceive('fetchLatestForTrafficAreaAndType')
            ->with('D', 'N&P')
            ->once()
            ->andReturn($publicationMock);

        $this->repoMap['PublicationLink']
            ->shouldReceive('fetchSingleUnpublished')
            ->with(m::type(UnpublishedBusRegQry::class))
            ->andReturn($publicationLinkMock)
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
     * @return array
     */
    public static function handleCommandCreateProvider(): array
    {
        return [
            [BusRegEntity::STATUS_NEW, 'Y'],
            [BusRegEntity::STATUS_NEW, 'N'],
            [BusRegEntity::STATUS_VAR, 'Y'],
            [BusRegEntity::STATUS_VAR, 'N'],
            [BusRegEntity::STATUS_CANCEL, 'Y'],
            [BusRegEntity::STATUS_CANCEL, 'N']
        ];
    }
}
