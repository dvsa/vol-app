<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Transfer\Command\Document\CopyDocument as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\CopyDocument as CommandHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\BusRegSearchView as BusRegSearchViewRepo;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\View\BusRegSearchView as BusRegSearchViewEntity;
use Dvsa\Olcs\Api\Domain\Command\Document\CreateDocumentSpecific as CreateDocumentSpecificCmd;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;

/**
 * Copy document test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CopyDocumentTest extends AbstractCommandHandlerTestCase
{
    /** @var  m\MockInterface */
    private $mockUploader;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Document', DocumentRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('BusRegSearchView', BusRegSearchViewRepo::class);
        $this->mockRepo('Cases', CasesRepo::class);
        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('TransportManager', TransportManagerRepo::class);
        $this->mockRepo('Publication', PublicationRepo::class);
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockUploader = m::mock(ContentStoreFileUploader::class);
        $this->mockedSmServices['FileUploader'] = $this->mockUploader;

        parent::setUp();
    }

    public function testHandleCommandWithAppplication(): void
    {
        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::APP,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(3)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchWithLicence')
            ->with(1)
            ->andReturn($mockEntity)
            ->once()
            ->getMock();

        $mockDocument = $this->getMockDocument();

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $params = [
            'description' => 'description',
            'category' => 4,
            'subCategory' => 5,
            'issuedDate' => '2015-01-01',
            'isScan' => true,
            'isExternal' => true,
            'application' => 1,
            'licence' => 3,
            'size' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'irhpApplication' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null
        ];

        $this->mockCopyDocumentsCommands($params);

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) copied', $res['messages'][0]);
    }

    public function testHandleCommandWithException(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::APP,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(3)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchWithLicence')
            ->with(1)
            ->andReturn($mockEntity)
            ->once()
            ->getMock();

        $mockDocument = $this->getMockDocument(null, 1);

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $params = [
            'description' => 'description',
            'category' => 4,
            'subCategory' => 5,
            'issuedDate' => '2015-01-01',
            'isScan' => true,
            'isExternal' => true,
            'application' => 1,
            'licence' => 3,
            'size' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'irhpApplication' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null
        ];

        $this->mockCopyDocumentsCommands($params, true);

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) copied', $res['messages'][0]);
    }

    public function testHandleCommandWithLicence(): void
    {
        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::LIC,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock()
            ->shouldReceive('getId')
            ->andReturn(3)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchByLicNo')
            ->with(1)
            ->andReturn($mockEntity)
            ->once()
            ->getMock();

        $mockDocument = $this->getMockDocument();

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $params = [
            'description' => 'description',
            'category' => 4,
            'subCategory' => 5,
            'issuedDate' => '2015-01-01',
            'isScan' => true,
            'isExternal' => true,
            'application' => null,
            'licence' => 3,
            'size' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'irhpApplication' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null
        ];

        $this->mockCopyDocumentsCommands($params);

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) copied', $res['messages'][0]);
    }

    public function testHandleCommandWithBusReg(): void
    {
        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::BUSREG,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock(BusRegSearchViewEntity::class)
            ->shouldReceive('getLicId')
            ->andReturn(3)
            ->once()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->getMock();

        $this->repoMap['BusRegSearchView']
            ->shouldReceive('fetchByRegNo')
            ->with(1)
            ->andReturn($mockEntity)
            ->once()
            ->getMock();

        $mockDocument = $this->getMockDocument();

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $params = [
            'description' => 'description',
            'category' => 4,
            'subCategory' => 5,
            'issuedDate' => '2015-01-01',
            'isScan' => true,
            'isExternal' => true,
            'application' => null,
            'licence' => 3,
            'size' => null,
            'busReg' => 1,
            'case' => null,
            'irfoOrganisation' => null,
            'irhpApplication' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null
        ];
        $this->mockCopyDocumentsCommands($params);

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) copied', $res['messages'][0]);
    }

    public function testHandleCommandWithCases(): void
    {
        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::CASES,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(33)
                ->once()
                ->getMock()
            )
            ->twice()
            ->shouldReceive('getApplication')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(44)
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->shouldReceive('getTransportManager')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(55)
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->getMock();

        $this->repoMap['Cases']
            ->shouldReceive('fetchExtended')
            ->with(1)
            ->andReturn($mockEntity)
            ->once()
            ->getMock();

        $mockDocument = $this->getMockDocument();

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $params = [
            'description' => 'description',
            'category' => 4,
            'subCategory' => 5,
            'issuedDate' => '2015-01-01',
            'isScan' => true,
            'isExternal' => true,
            'application' => 44,
            'licence' => 33,
            'size' => null,
            'busReg' => null,
            'case' => 1,
            'irfoOrganisation' => null,
            'irhpApplication' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => 55,
            'operatingCentre' => null,
            'opposition' => null
        ];

        $this->mockCopyDocumentsCommands($params);

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) copied', $res['messages'][0]);
    }

    public function testHandleCommandWithIrfo(): void
    {
        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::IRFO,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock();

        $this->repoMap['Organisation']
            ->shouldReceive('fetchById')
            ->with(1)
            ->andReturn($mockEntity)
            ->once()
            ->getMock();

        $mockDocument = $this->getMockDocument();

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $params = [
            'description' => 'description',
            'category' => 4,
            'subCategory' => 5,
            'issuedDate' => '2015-01-01',
            'isScan' => true,
            'isExternal' => true,
            'application' => null,
            'licence' => null,
            'size' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => 1,
            'irhpApplication' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null
        ];

        $this->mockCopyDocumentsCommands($params);

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) copied', $res['messages'][0]);
    }

    public function testHandleCommandWithIrhpApplication(): void
    {
        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::IRHP_APP,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock(IrhpApplicationEntity::class);
        $mockEntity->shouldReceive('getLicence->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->with(1)
            ->once()
            ->andReturn($mockEntity);

        $mockDocument = $this->getMockDocument();

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->once()
            ->andReturn($mockDocument);

        $params = [
            'description' => 'description',
            'category' => 4,
            'subCategory' => 5,
            'issuedDate' => '2015-01-01',
            'isScan' => true,
            'isExternal' => true,
            'application' => null,
            'licence' => 3,
            'size' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'irhpApplication' => 1,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null
        ];

        $this->mockCopyDocumentsCommands($params);

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) copied', $res['messages'][0]);
    }

    public function testHandleCommandWithTransportManager(): void
    {
        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::TM,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock();

        $this->repoMap['TransportManager']
            ->shouldReceive('fetchById')
            ->with(1)
            ->andReturn($mockEntity)
            ->once()
            ->getMock();

        $mockDocument = $this->getMockDocument();

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $params = [
            'description' => 'description',
            'category' => 4,
            'subCategory' => 5,
            'issuedDate' => '2015-01-01',
            'isScan' => true,
            'isExternal' => true,
            'application' => null,
            'licence' => null,
            'size' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'irhpApplication' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => 1,
            'operatingCentre' => null,
            'opposition' => null
        ];

        $this->mockCopyDocumentsCommands($params);

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) copied', $res['messages'][0]);
    }

    /**
     * Tests copying a publication document
     */
    public function testHandleCommandWithPublication(): void
    {
        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::PUBLICATION,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock(PublicationEntity::class);

        $mockTa = m::mock(TrafficAreaEntity::class);
        $mockTa->shouldReceive('getId')->once()->andReturn(6);

        $this->repoMap['Publication']
            ->shouldReceive('fetchById')
            ->with(1)
            ->andReturn($mockEntity)
            ->once()
            ->getMock();

        $mockDocument = $this->getMockDocument($mockTa);

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $params = [
            'description' => 'description',
            'category' => 4,
            'subCategory' => 5,
            'issuedDate' => '2015-01-01',
            'isScan' => true,
            'isExternal' => true,
            'application' => null,
            'licence' => null,
            'size' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'irhpApplication' => null,
            'submission' => null,
            'trafficArea' => 6,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null
        ];

        $this->mockCopyDocumentsCommands($params);

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) copied', $res['messages'][0]);
    }

    public function testHandleCommandWithWrongType(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'targetId' => 1,
            'type'     => 'wrong',
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithInvalidEntity(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'targetId' => 1,
            'type'     => CommandHandler::APP,
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $this->repoMap['Application']
            ->shouldReceive('fetchWithLicence')
            ->with(1)
            ->andThrow(NotFoundException::class)
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }

    protected function getMockDocument(mixed $trafficArea = null, int $getIdentifierCount = 2): m\MockInterface
    {
        return m::mock()
            ->shouldReceive('getIdentifier')
            ->andReturn('identifier')
            ->times($getIdentifierCount)
            ->shouldReceive('getTrafficArea')
            ->andReturn($trafficArea)
            ->once()
            ->shouldReceive('getDescription')
            ->andReturn('description')
            ->once()
            ->shouldReceive('getCategory')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(4)
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('getSubCategory')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(5)
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('getIssuedDate')
            ->andReturn('2015-01-01')
            ->once()
            ->shouldReceive('getIsScan')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getIsExternal')
            ->andReturn(true)
            ->once()
            ->getMock();
    }

    protected function mockCopyDocumentsCommands(mixed $params, bool $emptyDownload = false): void
    {
        if (!$emptyDownload) {
            $mockDownloadedFile = m::mock()
                ->shouldReceive('getResource')
                ->andReturn('resource')
                ->once()
                ->shouldReceive('getMimeType')
                ->andReturn('mimeType')
                ->once()
                ->getMock();

            $uploadData = array_merge(
                $params,
                [
                    'content' => [
                        'tmp_name' => 'resource',
                        'type'     => 'mimeType'
                    ],
                    'filename'         => 'identifier',
                    'shouldUploadOnly' => true
                ]
            );

            $this->mockUploader
                ->shouldReceive('download')
                ->with('identifier')
                ->andReturn($mockDownloadedFile)
                ->once();

            $uploadResult = new Result();
            $uploadResult->addId('identifier', 'identifier');

            $createData = array_merge(
                $params,
                [
                    'identifier' => 'identifier',
                    'filename'   => 'identifier'
                ]
            );

            $createResult = new Result();
            $createResult->addId('document', 111);
            $createResult->addMessage('Document(s) copied');

            $this->expectedSideEffect(UploadCmd::class, $uploadData, $uploadResult);
            $this->expectedSideEffect(CreateDocumentSpecificCmd::class, $createData, $createResult);

            return;
        }

        $this->mockUploader
            ->shouldReceive('download')
            ->with('identifier')
            ->andReturn(null)
            ->once();
    }
}
