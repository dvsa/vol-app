<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Exception\EbsrPackException;
use Dvsa\Olcs\Api\Domain\Exception\TransxchangeException;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\Ebsr\S3Processor;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessRequestMap as ProcessRequestMapCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\ProcessRequestMap;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\UpdateTxcInboxPdf as UpdateTxcInboxPdfCmd;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient;
use Olcs\XmlTools\Xml\TemplateBuilder;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessorInterface;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessor;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use org\bovigo\vfs\vfsStream;

class ProcessRequestMapTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProcessRequestMap();
        $this->mockRepo('Bus', BusRepo::class);

        $config = [
            'ebsr' => [
                'transexchange_publisher' => [
                    'templates' => [
                        TransExchangeClient::REQUEST_MAP_TEMPLATE => 'template path'
                    ],
                ],
                'tmp_extra_path' => '/tmp/file/path'
            ]
        ];

        $toggleService = m::mock(ToggleService::class);
        $toggleService->expects('isEnabled')->with(FeatureToggle::BACKEND_TRANSXCHANGE)->andReturnFalse();

        $this->mockedSmServices = [
            TemplateBuilder::class => m::mock(TemplateBuilder::class),
            FileProcessorInterface::class => m::mock(FileProcessor::class)->makePartial(),
            S3Processor::class => m::mock(S3Processor::class),
            TransExchangeClient::class => m::mock(TransExchangeClient::class),
            'config' => $config,
            ToggleService::class => $toggleService,
            'FileUploader' => m::mock(ContentStoreFileUploader::class)
        ];

        parent::setUp();
    }

    /**
     * testHandleCommand for a cancellation
     *
     * @dataProvider handleCommandCancellationProvider
     */
    public function testHandleCommandCancellation($fromNewEbsr, $state, $getStatusTimes): void
    {
        //use different config from the rest of the tests
        $config = [
            'ebsr' => [
                'transexchange_publisher' => [
                    'templates' => [
                        TransExchangeClient::DVSA_RECORD_TEMPLATE => '/templates/record',
                        TransExchangeClient::REQUEST_MAP_TEMPLATE => '/templates/map',
                        TransExchangeClient::TIMETABLE_TEMPLATE => '/templates/timetable'
                    ],
                ],
                'tmp_extra_path' => '/tmp/file/path'
            ]
        ];

        $this->sut->setConfig($config);

        $id = 99;
        $licenceId = 77;
        $submissionId = 55;
        $documentIdentifier = 'identifier';
        $uploadedDocumentId = 55;
        $busRegNo = 'PB8593040/4896';

        $fileSystem = vfsStream::setup();
        $transxchangeFilename = vfsStream::url('root/transxchange.pdf');
        $transxchangeContent = 'doc content';
        $file = vfsStream::newFile('transxchange.pdf');
        $file->setContent($transxchangeContent);
        $fileSystem->addChild($file);

        $command = ProcessRequestMapCmd::create(
            [
                'id' => $id,
                'user' => 1,
                'fromNewEbsr' => $fromNewEbsr
            ]
        );

        $submission = m::mock(EbsrSubmissionEntity::class);
        $submission->shouldReceive('getDocument->getIdentifier')->andReturn($documentIdentifier);
        $submission->shouldReceive('getId')->andReturn($submissionId);

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getId')->times(3)->withNoArgs()->andReturn($id);
        $busReg->shouldReceive('getRegNo')->andReturn($busRegNo);
        $busReg->shouldReceive('getLicence->getId')->andReturn($licenceId);
        $busReg->shouldReceive('getEbsrSubmissions')->once()->andReturn(new ArrayCollection([$submission]));
        $busReg->shouldReceive('isCancellation')->once()->withNoArgs()->andReturn(true);
        $busReg->shouldReceive('isEbsrRefresh')->times($getStatusTimes)->withNoArgs()->andReturn(false);
        $busReg->shouldReceive('getStatus->getId')
            ->times($getStatusTimes)
            ->withNoArgs()
            ->andReturn(BusRegEntity::STATUS_CANCEL);

        $this->commonAssertions($command, $busReg, $documentIdentifier, $transxchangeFilename, 1);

        $docUploadResult = $this->docUploadResult($uploadedDocumentId);
        $documentDesc = $this->sut->getDocumentDescriptions()[TransExchangeClient::DVSA_RECORD_TEMPLATE];

        $this->documentSideEffect($transxchangeFilename, $id, $licenceId, $documentDesc, $docUploadResult);
        $this->txcInboxSideEffect($id, $uploadedDocumentId, ProcessRequestMap::TXC_INBOX_TYPE_PDF);
        $this->taskSideEffect($id, $licenceId, $this->taskSuccessDesc($state, $busRegNo, $documentDesc));

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * data provider for testHandleCommandCancellation
     */
    public function handleCommandCancellationProvider()
    {
        return [
            [true, 'cancellation', 1],
            [false, 'pdf files', 0],
        ];
    }

    /**
     * testHandleCommand where bus reg is not a cancellation
     *
     * @dataProvider handleCommandNotCancellationProvider
     */
    public function testHandleCommandNotCancellation(
        $fromNewEbsr,
        $isEbsrRefresh,
        $ebsrRefreshTimes,
        $state,
        $busRegStatus,
        $getStatusTimes
    ): void {
        //use different config from the rest of the tests
        $config = [
            'ebsr' => [
                'transexchange_publisher' => [
                    'templates' => [
                        TransExchangeClient::DVSA_RECORD_TEMPLATE => '/templates/record',
                        TransExchangeClient::REQUEST_MAP_TEMPLATE => '/templates/map',
                        TransExchangeClient::TIMETABLE_TEMPLATE => '/templates/timetable'
                    ],
                ],
                'tmp_extra_path' => '/tmp/file/path'
            ]
        ];

        $this->sut->setConfig($config);

        $id = 99;
        $licenceId = 77;
        $submissionId = 55;
        $documentIdentifier = 'identifier';
        $uploadedDocumentId1 = 55;
        $uploadedDocumentId2 = 56;
        $uploadedDocumentId3 = 57;
        $busRegNo = 'PB8593040/4896';

        $fileSystem = vfsStream::setup();
        $transxchangeFilename = vfsStream::url('root/transxchange.pdf');
        $transxchangeContent = 'doc content';
        $file = vfsStream::newFile('transxchange.pdf');
        $file->setContent($transxchangeContent);
        $fileSystem->addChild($file);

        $command = ProcessRequestMapCmd::create(
            [
                'id' => $id,
                'user' => 1,
                'fromNewEbsr' => $fromNewEbsr,
                'scale' => 'auto'
            ]
        );

        $submission = m::mock(EbsrSubmissionEntity::class);
        $submission->shouldReceive('getDocument->getIdentifier')->andReturn($documentIdentifier);
        $submission->shouldReceive('getId')->andReturn($submissionId);

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getId')->times(5)->withNoArgs()->andReturn($id);
        $busReg->shouldReceive('getRegNo')->andReturn($busRegNo);
        $busReg->shouldReceive('getLicence->getId')->times(4)->withNoArgs()->andReturn($licenceId);
        $busReg->shouldReceive('getEbsrSubmissions')->once()->andReturn(new ArrayCollection([$submission]));
        $busReg->shouldReceive('isCancellation')->once()->withNoArgs()->andReturn(false);
        $busReg->shouldReceive('isEbsrRefresh')->times($ebsrRefreshTimes)->withNoArgs()->andReturn($isEbsrRefresh);
        $busReg->shouldReceive('getStatus->getId')
            ->times($getStatusTimes)
            ->withNoArgs()
            ->andReturn($busRegStatus);

        $this->commonAssertions($command, $busReg, $documentIdentifier, $transxchangeFilename, 3);

        $docUploadResult1 = $this->docUploadResult($uploadedDocumentId1);
        $docUploadResult2 = $this->docUploadResult($uploadedDocumentId2);
        $docUploadResult3 = $this->docUploadResult($uploadedDocumentId3);

        $docDescs = $this->sut->getDocumentDescriptions();

        $documentDesc1 = $docDescs[TransExchangeClient::DVSA_RECORD_TEMPLATE];
        $documentDesc2 = $docDescs[TransExchangeClient::TIMETABLE_TEMPLATE];
        $documentDesc3 = $docDescs[TransExchangeClient::REQUEST_MAP_TEMPLATE] . ' (Auto Scale)';

        $this->documentSideEffect($transxchangeFilename, $id, $licenceId, $documentDesc1, $docUploadResult1);
        $this->documentSideEffect($transxchangeFilename, $id, $licenceId, $documentDesc2, $docUploadResult2);
        $this->documentSideEffect($transxchangeFilename, $id, $licenceId, $documentDesc3, $docUploadResult3);

        $this->txcInboxSideEffect($id, $uploadedDocumentId1, ProcessRequestMap::TXC_INBOX_TYPE_PDF);
        $this->txcInboxSideEffect($id, $uploadedDocumentId3, ProcessRequestMap::TXC_INBOX_TYPE_ROUTE);

        $documentDesc = $documentDesc1 . ', ' . $documentDesc2 . ', ' . $documentDesc3;
        $this->taskSideEffect($id, $licenceId, $this->taskSuccessDesc($state, $busRegNo, $documentDesc));

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * data provider for testHandleCommandNotCancellation
     */
    public function handleCommandNotCancellationProvider(): array
    {
        return [
            [true, false, 1, 'variation', BusRegEntity::STATUS_VAR, 1],
            [true, false, 1, 'application', 'other bus status', 1],
            [true, true, 1, 'data refresh', '', 0],
            [false, false, 0, 'pdf files', '', 0],
        ];
    }

    /**
     * testHandleCommand where documents are failing
     */
    public function testHandleCommandDocumentsFailing(): void
    {
        //use different config from the rest of the tests
        $config = [
            'ebsr' => [
                'transexchange_publisher' => [
                    'templates' => [
                        TransExchangeClient::DVSA_RECORD_TEMPLATE => '/templates/record',
                        TransExchangeClient::REQUEST_MAP_TEMPLATE => '/templates/map',
                        TransExchangeClient::TIMETABLE_TEMPLATE => '/templates/timetable'
                    ],
                ],
                'tmp_extra_path' => '/tmp/file/path'
            ]
        ];

        $this->sut->setConfig($config);

        $id = 99;
        $licenceId = 77;
        $submissionId = 55;
        $documentIdentifier = 'identifier';
        $busRegNo = 'PB8593040/4896';

        $command = ProcessRequestMapCmd::create(
            [
                'id' => $id,
                'user' => 1,
                'fromNewEbsr' => false,
                'scale' => 'auto'
            ]
        );

        $submission = m::mock(EbsrSubmissionEntity::class);
        $submission->shouldReceive('getDocument->getIdentifier')->andReturn($documentIdentifier);
        $submission->shouldReceive('getId')->andReturn($submissionId);

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getId')->times(2)->withNoArgs()->andReturn($id);
        $busReg->shouldReceive('getRegNo')->andReturn($busRegNo);
        $busReg->shouldReceive('getLicence->getId')->times(1)->withNoArgs()->andReturn($licenceId);
        $busReg->shouldReceive('getEbsrSubmissions')->once()->andReturn(new ArrayCollection([$submission]));
        $busReg->shouldReceive('isCancellation')->once()->withNoArgs()->andReturn(false);

        $this->commonAssertions($command, $busReg, $documentIdentifier, null, 3, false);

        $docDescs = $this->sut->getDocumentDescriptions();

        $documentDesc1 = $docDescs[TransExchangeClient::DVSA_RECORD_TEMPLATE];
        $documentDesc2 = $docDescs[TransExchangeClient::TIMETABLE_TEMPLATE];
        $documentDesc3 = $docDescs[TransExchangeClient::REQUEST_MAP_TEMPLATE] . ' (Auto Scale)';

        $taskDesc = 'New pdf files created: '
            . $busRegNo . "\n"
            . 'The following PDFs failed to generate: '
            . $documentDesc1 . ', ' . $documentDesc2 . ', ' . $documentDesc3;
        $this->taskSideEffect($id, $licenceId, $taskDesc);

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * test handleCommand when template config is not found
     */
    public function testHandleCommandMissingEbsrPack(): void
    {
        $this->expectException(TransxchangeException::class);
        $this->expectExceptionMessage(ProcessRequestMap::MISSING_PACK_FILE_ERROR);

        $config = [
            'ebsr' => [
                'transexchange_publisher' => [
                    'templates' => [],
                ],
                'tmp_extra_path' => '/tmp/file/path'
            ]
        ];

        $this->sut->setConfig($config);

        $id = 99;
        $documentIdentifier = 'identifier';

        $command = ProcessRequestMapCmd::Create(
            [
                'id' => $id,
                'user' => 1,
            ]
        );

        $submission = m::mock(EbsrSubmissionEntity::class);
        $submission->shouldReceive('getDocument->getIdentifier')->andReturn($documentIdentifier);

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getEbsrSubmissions')->once()->andReturn(new ArrayCollection([$submission]));

        $this->busEntity($command, $busReg);

        $this->mockedSmServices[FileProcessorInterface::class]
            ->shouldReceive('fetchXmlFileNameFromDocumentStore')
            ->once()
            ->with($documentIdentifier, true)
            ->andThrow(EbsrPackException::class);

        $this->sut->handleCommand($command);
    }

    /**
     * test handleCommand when template config is not found
     */
    public function testHandleCommandMissingTemplateConfig(): void
    {
        $config = [
            'ebsr' => [
                'transexchange_publisher' => [
                    'templates' => [],
                ],
                'tmp_extra_path' => '/tmp/file/path'
            ]
        ];

        $this->sut->setConfig($config);

        $id = 99;
        $licenceId = 77;
        $busRegNo = 'PB8593040/4896';
        $submissionId = 55;
        $scale = 'small';
        $xmlFilename = 'filename.xml';
        $documentIdentifier = 'identifier';

        $command = ProcessRequestMapCmd::Create(
            [
                'id' => $id,
                'scale' => $scale,
                'user' => 1,
                'fromNewEbsr' => false
            ]
        );

        $submission = m::mock(EbsrSubmissionEntity::class);
        $submission->shouldReceive('getDocument->getIdentifier')->andReturn($documentIdentifier);
        $submission->shouldReceive('getId')->andReturn($submissionId);

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getEbsrSubmissions')->once()->andReturn(new ArrayCollection([$submission]));
        $busReg->shouldReceive('isCancellation')->once()->withNoArgs()->andReturn(true);
        $busReg->shouldReceive('getId')->times(2)->withNoArgs()->andReturn($id);
        $busReg->shouldReceive('getRegNo')->once()->withNoArgs()->andReturn($busRegNo);
        $busReg->shouldReceive('getLicence->getId')->andReturn($licenceId);

        $this->busEntity($command, $busReg);

        $this->mockedSmServices[FileProcessorInterface::class]
            ->shouldReceive('fetchXmlFileNameFromDocumentStore')
            ->once()
            ->with($documentIdentifier, true)
            ->andReturn($xmlFilename);

        $taskDesc = 'New pdf files created: '
            . $busRegNo . "\n"
            . 'The following PDFs failed to generate: DVSA Record PDF';
        $this->taskSideEffect($id, $licenceId, $taskDesc);

        $this->sut->handleCommand($command);
    }

    /**
     * test handleCommand throws an exception when config not found
     */
    public function testHandleCommandMissingFilePathConfig(): void
    {
        $this->expectException(TransxchangeException::class);
        $this->expectExceptionMessage('No tmp directory specified in config');

        $command = ProcessRequestMapCmd::create([]);
        $this->sut->setConfig([]);
        $this->sut->handleCommand($command);
    }

    /**
     * @param $state
     * @param $regNo
     * @param $desc
     *
     * @return string
     */
    private function taskSuccessDesc($state, $regNo, $desc): string
    {
        return 'New ' . $state . ' created: ' . $regNo . "\n" . 'The following PDFs were generated: ' . $desc;
    }

    /**
     * @param $uploadedDocumentId
     * @return Result
     */
    private function docUploadResult($uploadedDocumentId): Result
    {
        $docUploadResult = new Result();
        $docUploadResult->addId('document', $uploadedDocumentId);

        return $docUploadResult;
    }

    /**
     * @param $command
     * @param $busReg
     */
    private function busEntity($command, $busReg): void
    {
        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($busReg);
    }

    /**
     * @param $command
     * @param $busReg
     * @param $documentIdentifier
     * @param $numRequests
     */
    private function commonAssertions(
        $command,
        $busReg,
        $documentIdentifier,
        $transxchangeFilename,
        $numRequests,
        $returnDocument = true
    ) {
        $xmlFilename = 'filename.xml';

        $transXchangeDocument = [
            'files' => [
                0 => $transxchangeFilename
            ]
        ];

        $xmlTemplate = '<xml></xml>';

        $this->busEntity($command, $busReg);

        $this->mockedSmServices[FileProcessorInterface::class]
            ->shouldReceive('fetchXmlFileNameFromDocumentStore')
            ->once()
            ->with($documentIdentifier, true)
            ->andReturn($xmlFilename);

        $this->mockedSmServices[TemplateBuilder::class]
            ->shouldReceive('buildTemplate')
            ->times($numRequests)
            ->andReturn($xmlTemplate);

        $this->mockedSmServices[TransExchangeClient::class]
            ->shouldReceive('makeRequest')
            ->times($numRequests)
            ->with($xmlTemplate)
            ->andReturn($returnDocument ? $transXchangeDocument : []);
    }

    /**
     * creates a txc inbox side effect (avoids doing so for timetables)
     *
     * @param $id
     * @param $uploadedDocumentId
     * @param $pdfType
     */
    private function txcInboxSideEffect($id, $uploadedDocumentId, $pdfType): void
    {
        if ($pdfType !== '') {
            $txcPdfData = [
                'id' => $id,
                'document' => $uploadedDocumentId,
                'pdfType' => $pdfType
            ];

            $this->expectedSideEffect(UpdateTxcInboxPdfCmd::class, $txcPdfData, new Result());
        }
    }

    /**
     * @param $transxchangeFilename
     * @param $busRegId
     * @param $licenceId
     * @param $documentDesc
     * @param $uploadResult
     */
    private function documentSideEffect($transxchangeFilename, $busRegId, $licenceId, $documentDesc, $uploadResult): void
    {
        $documentData = [
            'content' => base64_encode(file_get_contents($transxchangeFilename)),
            'busReg' => $busRegId,
            'licence' => $licenceId,
            'category' => CategoryEntity::CATEGORY_BUS_REGISTRATION,
            'subCategory' => CategoryEntity::BUS_SUB_CATEGORY_TRANSXCHANGE_PDF,
            'filename' => basename((string) $transxchangeFilename),
            'description' => $documentDesc,
            'user' => 1
        ];

        $this->expectedSideEffect(UploadCmd::class, $documentData, $uploadResult);
    }

    /**
     * @param $busRegId
     * @param $licenceId
     * @param $taskDesc
     */
    private function taskSideEffect($busRegId, $licenceId, $taskDesc): void
    {
        $taskData = [
            'category' => TaskEntity::CATEGORY_BUS,
            'subCategory' => TaskEntity::SUBCATEGORY_EBSR,
            'description' => $taskDesc,
            'actionDate' => date('Y-m-d'),
            'busReg' => $busRegId,
            'licence' => $licenceId,
        ];

        $this->expectedSideEffect(CreateTaskCmd::class, $taskData, new Result());
    }
}
