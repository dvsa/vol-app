<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\PrintScheduler;

use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\PrintJob as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\PrintScheduler\PrintJob as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\Exception;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer;
use Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Service\ConvertToPdf\WebServiceClient;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class PrintJobTest extends AbstractCommandHandlerTestCase
{
    public $mockFileUploader;
    public $config;
    /** @var  CommandHandler | m\MockInterface */
    protected $sut;

    /** @var m\MockInterface | \Dvsa\Olcs\Api\Service\ConvertToPdf\WebServiceClient  */
    private $convertToPdfService;
    /** @var m\MockInterface | \Dvsa\Olcs\Api\Entity\User\User */
    private $mockUser;

    public function setUp(): void
    {
        $this->sut = m::mock(CommandHandler::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->mockRepo('Document', \Dvsa\Olcs\Api\Domain\Repository\Document::class);
        $this->mockRepo('User', \Dvsa\Olcs\Api\Domain\Repository\User::class);
        $this->mockRepo('SystemParameter', \Dvsa\Olcs\Api\Domain\Repository\SystemParameter::class);
        $this->mockRepo('Printer', \Dvsa\Olcs\Api\Domain\Repository\Printer::class);

        $this->mockFileUploader = m::mock(ContentStoreFileUploader::class);

        $this->config = [
            'print' => [
                'server' => 'PRINT_SERVER',
                'options' => [
                    'user' => 'PRINT_USER',
                ],
            ]
        ];

        $this->convertToPdfService = m::mock(WebServiceClient::class);
        $this->sut->__construct($this->config, $this->mockFileUploader, $this->convertToPdfService);

        $this->mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $this->mockUser->setLoginId('LOGIN_ID');
        $this->repoMap['User']->shouldReceive('fetchById')->with('USER_ID')->andReturn($this->mockUser);

        /** @var Document $mockDocument */
        $mockDocument = m::mock(Document::class)->makePartial();
        $mockDocument->setIdentifier('IDENTIFIER');
        $mockDocument->setFilename('FILENAME');
        $mockDocument->setDescription('DESC');
        $this->repoMap['Document']->shouldReceive('fetchById')->with('DOC_ID')->once()->andReturn($mockDocument);

        /** @var Document $mockDocument */
        $mockDocument2 = m::mock(Document::class)->makePartial();
        $mockDocument2->setIdentifier('IDENTIFIER2');
        $mockDocument2->setFilename('FILENAME2');
        $mockDocument2->setDescription('DESC2');
        $this->repoMap['Document']->shouldReceive('fetchById')->with('DOC2_ID')->andReturn($mockDocument2);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
        ];

        $this->references = [
            Licence::class => [
                34 => m::mock(Licence::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandNoUser(): void
    {
        $command = Cmd::create(
            [
                'id' => 'QUEUE_ID',
                'documents' => ['DOC_ID'],
                'title' => 'JOB',
                'copies' => 999,
            ]
        );

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()->andReturn('QUEUE1');

        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->expectPrintFile(0, true, 0, 'PRINT_USER', 999);

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Printed successfully"], $result->getMessages());
    }

    public function testHandleCommandConvertUsingWebService(): void
    {
        $this->config = [
            'print' => ['server' => 'PRINT_SERVER'],
            'convert_to_pdf' => ['uri' => 'http://web.com:8080/foo']
        ];
        $this->setSutConfig($this->config);
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => '']);

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()->andReturn('QUEUE1');

        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->convertToPdfService->shouldReceive('convert')->with('TEMP_FILE.rtf', 'TEMP_FILE.pdf')->once();

        $this->expectLpr('Anonymous', 0, true, 1);

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Printed successfully"], $result->getMessages());
    }

    public function testHandleCommandConvertUsingWebServiceError(): void
    {
        $this->config = [
            'print' => ['server' => 'PRINT_SERVER'],
            'convert_to_pdf' => ['uri' => 'http://web.com:8080/foo']
        ];
        $this->setSutConfig($this->config);
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => '']);

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()->andReturn('QUEUE1');

        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->convertToPdfService->shouldReceive('convert')->with('TEMP_FILE.rtf', 'TEMP_FILE.pdf')->once()
            ->andThrow(\Dvsa\Olcs\Api\Domain\Exception\RestResponseException::class, 'TEST MESSAGE');

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $this->expectException(NotReadyException::class);
        $this->expectExceptionMessage('Error generating the PDF TEMP_FILE.rtf : TEST MESSAGE');
        $this->sut->handleCommand($command);
    }

    /**
     * Test https://jira.i-env.net/browse/OLCS-15140 Convert to PDF timeout
     */
    public function testHandleCommandConvertUsingWebServiceTimeout(): void
    {
        $this->config = [
            'print' => ['server' => 'PRINT_SERVER'],
            'convert_to_pdf' => ['uri' => 'http://web.com:8080/foo']
        ];
        $this->setSutConfig($this->config);
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => '']);

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()->andReturn('QUEUE1');

        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->convertToPdfService->shouldReceive('convert')->with('TEMP_FILE.rtf', 'TEMP_FILE.pdf')->once()
            ->andThrow(\Laminas\Http\Client\Adapter\Exception\TimeoutException::class, 'Timeout');

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $this->expectException(NotReadyException::class);
        $this->expectExceptionMessage('Error generating the PDF TEMP_FILE.rtf : Timeout');
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandSelfserveUser(): void
    {
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => 'USER_ID']);

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()->andReturn('QUEUE1');

        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->expectPrintFile();

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Printed successfully"], $result->getMessages());
    }

    public function testHandleCommandPdfCreateError(): void
    {
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => 'USER_ID']);

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()->andReturn('QUEUE1');

        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->expectPrintFile(1);

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $this->expectException(NotReadyException::class);
        $this->expectExceptionMessage('Error generating the PDF : OUTPUT PDF');
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandPdfMissing(): void
    {
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => 'USER_ID']);

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()->andReturn('QUEUE1');

        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->expectPrintFile(0, false);

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $this->expectException(NotReadyException::class);
        $this->expectExceptionMessage('PDF file does not exist : TEMP_FILE.pdf');
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandPrintCreateError(): void
    {
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => 'USER_ID']);

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()->andReturn('QUEUE1');

        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->expectPrintFile(0, true, 1);

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $this->expectException(NotReadyException::class);
        $this->expectExceptionMessage('Error executing lpr command : OUTPUT LPR');
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandInternalUser(): void
    {
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => 'USER_ID']);

        $team = new Team();
        $printer = new Printer();
        $printer->setPrinterName('QUEUE1');
        $teamPrinter = new TeamPrinter($team, $printer);
        $team->addTeamPrinters($teamPrinter);
        $this->mockUser->setTeam($team);

        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->expectPrintFile();

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Printed successfully"], $result->getMessages());
    }

    public function testHandleCommandInternalUserMultiDocs(): void
    {
        $command = Cmd::create(
            ['id' => 'QUEUE_ID', 'documents' => ['DOC_ID', 'DOC2_ID'], 'title' => 'JOB', 'user' => 'USER_ID']
        );

        $team = new Team();
        $printer = new Printer();
        $printer->setPrinterName('QUEUE1');
        $teamPrinter = new TeamPrinter($team, $printer);
        $team->addTeamPrinters($teamPrinter);
        $this->mockUser->setTeam($team);

        // 1st file
        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->sut->shouldReceive('executeCommand')
            ->with("soffice --headless --convert-to pdf:writer_pdf_Export --outdir /tmp 'TEMP_FILE.rtf' 2>&1", [], 0)
            ->once()
            ->andReturnUsing(
                function ($command, &$output, &$result) {
                    $result = 0;
                    $output = ['OUTPUT PDF'];
                }
            );

        // 2nd file
        $mockFile2 = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER2')->once()
            ->andReturn($mockFile2);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile2, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME2')
            ->once()
            ->andReturn('TEMP_FILE2.rtf');

        $this->sut->shouldReceive('executeCommand')
            ->with(
                "soffice --headless --convert-to pdf:writer_pdf_Export --outdir /tmp 'TEMP_FILE2.rtf' 2>&1",
                [],
                0
            )
            ->once()
            ->andReturnUsing(
                function ($command, &$output, &$result) {
                    $result = 0;
                    $output = ['OUTPUT PDF2'];
                }
            );

        // both pdf files to be merged into one
        $this->sut->shouldReceive('executeCommand')
            ->with(
                "pdfunite 'TEMP_FILE.pdf' 'TEMP_FILE2.pdf' '/tmp/PrintJob-QUEUE_ID-print.pdf' 2>&1",
                [],
                0
            )
            ->once()
            ->andReturnUsing(
                function ($command, &$output, &$result) {
                    $result = 0;
                    $output = ['PDF FILES MERGED'];
                }
            );

        // the file to be printed
        $this->sut->shouldReceive('fileExists')->with('/tmp/PrintJob-QUEUE_ID-print.pdf')->once()->andReturn(true);

        $this->sut->shouldReceive('executeCommand')
            ->with(
                "lpr '/tmp/PrintJob-QUEUE_ID-print.pdf'" .
                " -H 'PRINT_SERVER'" .
                " -C 'PrintJob-QUEUE_ID-print.pdf'" .
                " -h -P 'QUEUE1'" .
                " -U 'PRINT_USER'" .
                " -#1" .
                " -o collate=true" .
                " 2>&1",
                [],
                0
            )->once()
            ->andReturnUsing(
                function ($command, &$output, &$result) {
                    $result = 0;
                    $output = ['OUTPUT LPR'];
                }
            );

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Printed successfully'], $result->getMessages());
    }

    public function testHandleCommandInternalUserMultiDocsMergeError(): void
    {
        $command = Cmd::create(
            ['id' => 'QUEUE_ID', 'documents' => ['DOC_ID', 'DOC2_ID'], 'title' => 'JOB', 'user' => 'USER_ID']
        );

        $team = new Team();
        $printer = new Printer();
        $printer->setPrinterName('QUEUE1');
        $teamPrinter = new TeamPrinter($team, $printer);
        $team->addTeamPrinters($teamPrinter);
        $this->mockUser->setTeam($team);

        // 1st file
        $mockFile = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn($mockFile);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME')
            ->once()
            ->andReturn('TEMP_FILE.rtf');

        $this->sut->shouldReceive('executeCommand')
            ->with("soffice --headless --convert-to pdf:writer_pdf_Export --outdir /tmp 'TEMP_FILE.rtf' 2>&1", [], 0)
            ->once()
            ->andReturnUsing(
                function ($command, &$output, &$result) {
                    $result = 0;
                    $output = ['OUTPUT PDF'];
                }
            );

        // 2nd file
        $mockFile2 = m::mock(File::class);
        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER2')->once()
            ->andReturn($mockFile2);

        $this->sut->shouldReceive('createTmpFile')
            ->with($mockFile2, CommandHandler::TEMP_FILE_PREFIX . '-QUEUE_ID-', 'FILENAME2')
            ->once()
            ->andReturn('TEMP_FILE2.rtf');

        $this->sut->shouldReceive('executeCommand')
            ->with(
                "soffice --headless --convert-to pdf:writer_pdf_Export --outdir /tmp 'TEMP_FILE2.rtf' 2>&1",
                [],
                0
            )
            ->once()
            ->andReturnUsing(
                function ($command, &$output, &$result) {
                    $result = 0;
                    $output = ['OUTPUT PDF2'];
                }
            );

        // both pdf files to be merged into one
        $this->sut->shouldReceive('executeCommand')
            ->with(
                "pdfunite 'TEMP_FILE.pdf' 'TEMP_FILE2.pdf' '/tmp/PrintJob-QUEUE_ID-print.pdf' 2>&1",
                [],
                0
            )
            ->once()
            ->andReturnUsing(
                function ($command, &$output, &$result) {
                    $result = 1;
                    $output = ['PDF MERGE ERROR'];
                }
            );

        // the file NOT to be printed
        $this->sut->shouldReceive('fileExists')->never();

        $this->sut->shouldReceive('executeCommand')
            ->with(
                "lpr '/tmp/PrintJob-QUEUE_ID-print.pdf'" .
                " -H 'PRINT_SERVER'" .
                " -C 'PrintJob-QUEUE_ID-print.pdf'" .
                " -h -P 'QUEUE1'" .
                " -U 'PRINT_USER'" .
                " -#1" .
                " -o collate=true" .
                " 2>&1",
                [],
                0
            )->never();

        $this->sut->shouldReceive('deleteTempFiles')->withNoArgs()->once();

        $this->expectException(NotReadyException::class);
        $this->expectExceptionMessage('Error executing pdfunite command : PDF MERGE ERROR');

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandCannotDownloadFile(): void
    {
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => 'USER_ID']);

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()->andReturn('QUEUE1');

        $this->mockFileUploader->shouldReceive('download')->with('IDENTIFIER')->once()
            ->andReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Can't find document");
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandInternalUserNoPrinter(): void
    {
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => 'USER_ID']);

        $team = new Team();
        $this->mockUser->setTeam($team);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot find printer for User LOGIN_ID');
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandStubLicence(): void
    {
        $command = Cmd::create(['id' => 'QUEUE_ID', 'documents' => ['DOC_ID'], 'title' => 'JOB', 'user' => 'USER_ID']);

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()
            ->andReturn('TESTING-STUB-LICENCE:34');

        $this->repoMap['Document']->shouldReceive('save')->once()->andReturnUsing(
            function ($document) {
                $this->assertSame('PRINT DESC', $document->getDescription());
                $this->assertSame(34, $document->getLicence()->getId());
            }
        );

        $this->mockFileUploader->shouldReceive('download')->never();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Printed successfully (stub to licence 34)"], $result->getMessages());
    }

    public function testHandleCommandStubLicenceMultiDocs(): void
    {
        $command = Cmd::create(
            ['id' => 'QUEUE_ID', 'documents' => ['DOC_ID', 'DOC2_ID'], 'title' => 'JOB', 'user' => 'USER_ID']
        );

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameter::SELFSERVE_USER_PRINTER)->once()
            ->andReturn('TESTING-STUB-LICENCE:34');

        $this->repoMap['Document']->shouldReceive('save')->once()->andReturnUsing(
            function ($document) {
                $this->assertSame('PRINT DESC', $document->getDescription());
                $this->assertSame(34, $document->getLicence()->getId());
            }
        );
        $this->repoMap['Document']->shouldReceive('save')->once()->andReturnUsing(
            function ($document) {
                $this->assertSame('PRINT DESC2', $document->getDescription());
                $this->assertSame(34, $document->getLicence()->getId());
            }
        );

        $this->mockFileUploader->shouldReceive('download')->never();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(["Printed successfully (stub to licence 34)"], $result->getMessages());
    }

    private function expectPrintFile(
        int $commandPdfResult = 0,
        bool $fileExists = true,
        int $commandLprResult = 0,
        string $userName = 'PRINT_USER',
        int $copies = 1
    ): void {
        $this->sut->shouldReceive('executeCommand')
            ->with("soffice --headless --convert-to pdf:writer_pdf_Export --outdir /tmp 'TEMP_FILE.rtf' 2>&1", [], 0)
            ->once()
            ->andReturnUsing(
                function ($command, &$output, &$result) use ($commandPdfResult) {
                    $result = $commandPdfResult;
                    $output = ['OUTPUT PDF'];
                }
            );
        if ($commandPdfResult !== 0) {
            return;
        }

        $this->expectLpr($userName, $commandLprResult, $fileExists, $copies);
    }

    private function setSutConfig(array $config): void
    {
        $reflection = new \ReflectionProperty(CommandHandler::class, 'config');
        $reflection->setValue($this->sut, $config);
    }

    private function expectLpr(mixed $userName, mixed $commandLprResult, mixed $fileExists, int $copies = 1): void
    {
        $this->sut->shouldReceive('fileExists')->with('TEMP_FILE.pdf')->once()->andReturn($fileExists);
        if (!$fileExists) {
            return;
        }

        $this->sut->shouldReceive('executeCommand')
            ->with(
                "lpr 'TEMP_FILE.pdf'" .
                " -H 'PRINT_SERVER'" .
                " -C 'TEMP_FILE.pdf'" .
                " -h -P 'QUEUE1'" .
                " -U '{$userName}'" .
                " -#{$copies}" .
                " -o collate=true" .
                " 2>&1",
                [],
                0
            )->once()
            ->andReturnUsing(
                function ($command, &$output, &$result) use ($commandLprResult) {
                    $result = $commandLprResult;
                    $output = ['OUTPUT LPR'];
                }
            );
    }
}
