<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Scan;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\Olcs\Api\Domain\CommandHandler\Scan\CreateDocument as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Scan\CreateDocument;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Document Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateDocumentTest extends AbstractCommandHandlerTestCase
{
    private $validPdf = '%PDF-1.2';

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Scan', \Dvsa\Olcs\Api\Domain\Repository\Scan::class);

        $this->mockedSmServices['FileUploader'] = m::mock(ContentStoreFileUploader::class);

        parent::setUp();
    }

    public function testHandleCommandInvalidMime(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'content' => base64_encode('<html></html>')
        ];

        $command = CreateDocument::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithoutScan(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'content' => base64_encode((string) $this->validPdf),
            'scanId' => 111
        ];

        $command = CreateDocument::create($data);

        $this->repoMap['Scan']->shouldReceive('fetchById')
            ->once()
            ->with(111)
            ->andThrow(NotFoundException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $data = [
            'content' => base64_encode((string) $this->validPdf),
            'scanId' => 111,
            'filename' => 'foo.pdf'
        ];

        $command = CreateDocument::create($data);

        $scan = new \Dvsa\Olcs\Api\Entity\PrintScan\Scan();
        $scan->setDateReceived(null);
        $scan->setId(124);
        $scan->setLicence(m::mock()->shouldReceive('getId')->andReturn(61)->getMock());
        $scan->setBusReg(m::mock()->shouldReceive('getId')->andReturn(62)->getMock());
        $scan->setCase(m::mock()->shouldReceive('getId')->andReturn(63)->getMock());
        $scan->setTransportManager(m::mock()->shouldReceive('getId')->andReturn(64)->getMock());
        $scan->setCategory(m::mock()->shouldReceive('getId')->andReturn(65)->getMock());
        $scan->setSubCategory(m::mock()->shouldReceive('getId')->andReturn(66)->getMock());
        $scan->setIrfoOrganisation(m::mock()->shouldReceive('getId')->andReturn(67)->getMock());
        $scan->setIrhpApplication(m::mock()->shouldReceive('getId')->andReturn(68)->getMock());
        $scan->setDescription('DESCRIPTION');

        $this->repoMap['Scan']->shouldReceive('fetchById')
            ->once()
            ->with(111)
            ->andReturn($scan)
            ->shouldReceive('delete')
            ->once()
            ->with($scan);

        $result = new Result();
        $result->addMessage('Upload');
        $data = [
            'content'          => base64_encode((string) $this->validPdf),
            'filename'         => 'foo.pdf',
            'description'      => 'DESCRIPTION',
            'isExternal'       => false,
            'isScan'           => true,
            'licence'          => 61,
            'busReg'           => 62,
            'case'             => 63,
            'transportManager' => 64,
            'category'         => 65,
            'subCategory'      => 66,
            'irfoOrganisation' => 67,
            'irhpApplication'  => 68,
        ];
        $this->expectedSideEffect(Upload::class, $data, $result);

        $result = new Result();
        $result->addMessage('CreateTask');
        $data = [
            'category' => 65,
            'subCategory' => 66,
            'description' => 'DESCRIPTION',
            'licence'          => 61,
            'busReg'           => 62,
            'case'             => 63,
            'transportManager' => 64,
            'irfoOrganisation' => 67,
            'irhpApplication'  => 68,
        ];
        $this->expectedSideEffect(CreateTask::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'scan' => 124,
            ],
            'messages' => [
                'Upload',
                'CreateTask',
                'Scan ID 124 document created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandBackScan(): void
    {
        $data = [
            'content' => base64_encode((string) $this->validPdf),
            'scanId' => 111,
            'filename' => 'foo.pdf'
        ];

        $dateReceived = '2020-09-04';

        $command = CreateDocument::create($data);

        $scan = new \Dvsa\Olcs\Api\Entity\PrintScan\Scan();
        $scan->setDateReceived(new DateTime($dateReceived));
        $scan->setId(124);
        $scan->setLicence(m::mock()->shouldReceive('getId')->andReturn(61)->getMock());
        $scan->setBusReg(m::mock()->shouldReceive('getId')->andReturn(62)->getMock());
        $scan->setCase(m::mock()->shouldReceive('getId')->andReturn(63)->getMock());
        $scan->setTransportManager(m::mock()->shouldReceive('getId')->andReturn(64)->getMock());
        $scan->setCategory(m::mock()->shouldReceive('getId')->andReturn(65)->getMock());
        $scan->setSubCategory(m::mock()->shouldReceive('getId')->andReturn(66)->getMock());
        $scan->setIrfoOrganisation(m::mock()->shouldReceive('getId')->andReturn(67)->getMock());
        $scan->setIrhpApplication(m::mock()->shouldReceive('getId')->andReturn(68)->getMock());
        $scan->setDescription('DESCRIPTION');

        $this->repoMap['Scan']->shouldReceive('fetchById')
            ->once()
            ->with(111)
            ->andReturn($scan)
            ->shouldReceive('delete')
            ->once()
            ->with($scan);

        $result = new Result();
        $result->addMessage('Upload');
        $data = [
            'content'          => base64_encode((string) $this->validPdf),
            'filename'         => 'foo.pdf',
            'description'      => 'DESCRIPTION (Back scan)',
            'isExternal'       => false,
            'isScan'           => true,
            'licence'          => 61,
            'busReg'           => 62,
            'case'             => 63,
            'transportManager' => 64,
            'category'         => 65,
            'subCategory'      => 66,
            'irfoOrganisation' => 67,
            'irhpApplication'  => 68,
            'issuedDate'       => $dateReceived,
        ];
        $this->expectedSideEffect(Upload::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'scan' => 124,
            ],
            'messages' => [
                'Upload',
                'Scan ID 124 document created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
