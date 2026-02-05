<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\ReportingBulkReprint;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\BulkReprint as BulkReprintCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class ReportingBulkReprintTest extends AbstractCommandHandlerTestCase
{
    public $mockFileUploader;
    public function setUp(): void
    {
        $this->sut = new ReportingBulkReprint();

        $this->mockFileUploader = m::mock(ContentStoreFileUploader::class);
        $this->mockedSmServices['FileUploader'] = $this->mockFileUploader;

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $documentIdentifier = 'documents/Report/Community_licence/2019/02/csvFilename.csv';
        $userId = 491;

        $this->mockFileUploader->shouldReceive('upload')
            ->once()
            ->andReturnUsing(function ($uploadPath, $file) {
                $expectedContent = "result line 1\r\nresult line 2\r\nresult line 3";
                $this->assertEquals('documents/Report/csvFilename.log', $uploadPath);
                $this->assertInstanceOf(ContentStoreFile::class, $file);
                $this->assertEquals($expectedContent, $file->getContent());
            });

        $bulkReprintResult = new Result();
        $bulkReprintResult->addMessage('result line 1')
            ->addMessage('result line 2')
            ->addMessage('result line 3');

        $this->expectedSideEffect(
            BulkReprintCmd::class,
            [
                'documentIdentifier' => $documentIdentifier,
                'user' => $userId,
            ],
            $bulkReprintResult
        );

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getDocumentIdentifier')
            ->andReturn($documentIdentifier);
        $command->shouldReceive('getUser')
            ->andReturn($userId);

        $result = $this->sut->handleCommand($command);

        $expectedMessages = ['File successfully uploaded to path documents/Report/csvFilename.log'];
        $this->assertEquals($expectedMessages, $result->getMessages());
    }
}
