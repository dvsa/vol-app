<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\BulkSend;

use Dvsa\Olcs\Api\Domain\Command\BulkSend\ProcessEmail;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\BulkSend\Email;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class EmailTest extends AbstractCommandHandlerTestCase
{
    private $mockFileUploader;

    public function setUp(): void
    {
        $this->sut = new Email();

        $this->mockFileUploader = m::mock(ContentStoreFileUploader::class);
        $this->mockedSmServices['FileUploader'] = $this->mockFileUploader;

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $documentIdentifier = 'documentIdentifier';

        $fileContents = "1\n" .
            "4\n" .
            "8\n" .
            "10\n" .
            "10\n";

        $file = m::mock(ContentStoreFile::class);
        $file->shouldReceive('getContent')
            ->once()
            ->withNoArgs()
            ->andReturn($fileContents);

        $this->mockFileUploader->shouldReceive('download')
            ->with($documentIdentifier)
            ->once()
            ->andReturn($file);

        $processResult = new Result();
        $processResult->addMessage('Sending Email');

        $this->expectedSideEffect(
            ProcessEmail::class,
            [
                'id' => 1,
                'templateName' => 'template-name'
            ],
            $processResult
        );

        $this->expectedSideEffect(
            ProcessEmail::class,
            [
                'id' => 4,
                'templateName' => 'template-name'
            ],
            $processResult
        );

        $this->expectedSideEffect(
            ProcessEmail::class,
            [
                'id' => 8,
                'templateName' => 'template-name'
            ],
            $processResult
        );

        $this->expectedSideEffect(
            ProcessEmail::class,
            [
                'id' => 10,
                'templateName' => 'template-name'
            ],
            $processResult
        );

        $command = m::mock(CommandInterface::class);

        $command->shouldReceive('getDocumentIdentifier')
            ->once()
            ->withNoArgs()
            ->andReturn($documentIdentifier);

        $command->shouldReceive('getTemplateName')
            ->once()
            ->withNoArgs()
            ->andReturn('template-name');

        $result = $this->sut->handleCommand($command);

        $expectedMessages = [
            'Sending Email',
            'Sending Email',
            'Sending Email',
            'Sending Email',
            'Processing completed successfully',
        ];

        $this->assertEquals($expectedMessages, $result->getMessages());
    }
}
