<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Exception\EbsrPackException;
use Dvsa\Olcs\Api\Domain\Exception\TransxchangeException;
use Dvsa\Olcs\Api\Service\Ebsr\S3Processor;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessRequestMap as ProcessRequestMapCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\ProcessRequestMap;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient;
use Olcs\XmlTools\Xml\TemplateBuilder;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessorInterface;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessor;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;

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

        $this->mockedSmServices = [
            TemplateBuilder::class => m::mock(TemplateBuilder::class),
            FileProcessorInterface::class => m::mock(FileProcessor::class)->makePartial(),
            S3Processor::class => m::mock(S3Processor::class),
            TransExchangeClient::class => m::mock(TransExchangeClient::class),
            'config' => $config,
            'FileUploader' => m::mock(ContentStoreFileUploader::class)
        ];

        parent::setUp();
    }

    /**
     * testHandleCommand for a cancellation
     */
    public function testHandleCommandCancellation(): void
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
        $submissionId = 55;
        $documentIdentifier = 'identifier';

        $command = ProcessRequestMapCmd::create(
            [
                'id' => $id,
                'user' => 1,
                'scale' => 'auto'
            ]
        );

        $submission = m::mock(EbsrSubmissionEntity::class);
        $submission->expects('getDocument->getIdentifier')->withNoArgs()->andReturn($documentIdentifier);
        $submission->expects('getId')->withNoArgs()->andReturn($submissionId);

        $busReg = m::mock(BusRegEntity::class);

        $busReg->expects('getEbsrSubmissions')->andReturn(new ArrayCollection([$submission]));
        $busReg->expects('isCancellation')->withNoArgs()->andReturnTrue();

        $this->commonAssertions($command, $busReg, $documentIdentifier, $submissionId, 1);

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * testHandleCommand where bus reg is not a cancellation
     */
    public function testHandleCommandNotCancellation(): void
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
        $submissionId = 55;
        $documentIdentifier = 'identifier';

        $command = ProcessRequestMapCmd::create(
            [
                'id' => $id,
                'user' => 1,
                'scale' => 'auto'
            ]
        );

        $submission = m::mock(EbsrSubmissionEntity::class);
        $submission->expects('getDocument->getIdentifier')->withNoArgs()->andReturn($documentIdentifier);
        $submission->expects('getId')->withNoArgs()->andReturn($submissionId);

        $busReg = m::mock(BusRegEntity::class);

        $busReg->expects('getEbsrSubmissions')->withNoArgs()->andReturn(new ArrayCollection([$submission]));
        $busReg->expects('isCancellation')->withNoArgs()->andReturnFalse();

        $this->commonAssertions($command, $busReg, $documentIdentifier, $submissionId, 3);

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
        $submission->expects('getDocument->getIdentifier')->withNoArgs()->andReturn($documentIdentifier);

        $busReg = m::mock(BusRegEntity::class);
        $busReg->expects('getEbsrSubmissions')->withNoArgs()->andReturn(new ArrayCollection([$submission]));

        $this->busEntity($command, $busReg);

        $this->mockedSmServices[FileProcessorInterface::class]
            ->expects('fetchXmlFileNameFromDocumentStore')
            ->with($documentIdentifier)
            ->andThrow(EbsrPackException::class);

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
        $submissionId,
        $numRequests,
    ): void {
        $xmlFilename = 'filename.xml';

        $s3Filename = $submissionId . '.xml';

        $xmlTemplate = '<xml></xml>';

        $this->busEntity($command, $busReg);

        $this->mockedSmServices[FileProcessorInterface::class]
            ->expects('fetchXmlFileNameFromDocumentStore')
            ->with($documentIdentifier)
            ->andReturn($xmlFilename);

        $this->mockedSmServices[S3Processor::class]
            ->expects('process')
            ->with($xmlFilename, ['s3Filename' => $s3Filename])
            ->andReturn($s3Filename);

        $this->mockedSmServices[TemplateBuilder::class]
            ->shouldReceive('buildTemplate')
            ->times($numRequests)
            ->andReturn($xmlTemplate);

        $this->mockedSmServices[TransExchangeClient::class]
            ->shouldReceive('makeRequest')
            ->times($numRequests)
            ->with($xmlTemplate);
    }
}
