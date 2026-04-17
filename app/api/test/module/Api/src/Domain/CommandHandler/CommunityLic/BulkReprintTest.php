<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\ValidatingReprintCaller as ValidatingReprintCallerCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\BulkReprint;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class BulkReprintTest extends AbstractCommandHandlerTestCase
{
    private $mockFileUploader;

    public function setUp(): void
    {
        $this->mockRepo('Document', DocumentRepo::class);

        $this->sut = new BulkReprint();

        $this->mockFileUploader = m::mock(ContentStoreFileUploader::class);
        $this->mockedSmServices['FileUploader'] = $this->mockFileUploader;

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $documentIdentifier = 'documentIdentifier';
        $userId = 491;

        $fileContents = "1,2,100\n" .
            "4,5,6,7\n" .
            "8,9\n" .
            "10,11,101\n" .
            "14,15,100\n" .
            "17,18,100\n" .
            "20,21,101\n";

        $file = m::mock(ContentStoreFile::class);
        $file->shouldReceive('getContent')
            ->andReturn($fileContents);

        $this->mockFileUploader->shouldReceive('download')
            ->with($documentIdentifier)
            ->andReturn($file);

        $licence100Result = new Result();
        $licence100Result->addMessage('Licence 100 result message 1');
        $licence100Result->addMessage('Licence 100 result message 2');

        $this->expectedSideEffect(
            ValidatingReprintCallerCmd::class,
            [
                'communityLicences' => [
                    ['communityLicenceId' => 1, 'communityLicenceIssueNo' => 2],
                    ['communityLicenceId' => 14, 'communityLicenceIssueNo' => 15],
                    ['communityLicenceId' => 17, 'communityLicenceIssueNo' => 18]
                ],
                'licence' => 100,
                'user' => 491
            ],
            $licence100Result
        );

        $licence101Result = new Result();
        $licence101Result->addMessage('Licence 101 result message 1');

        $this->expectedSideEffect(
            ValidatingReprintCallerCmd::class,
            [
                'communityLicences' => [
                    ['communityLicenceId' => 10, 'communityLicenceIssueNo' => 11],
                    ['communityLicenceId' => 20, 'communityLicenceIssueNo' => 21],
                ],
                'licence' => 101,
                'user' => 491,
            ],
            $licence101Result
        );

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getDocumentIdentifier')
            ->andReturn($documentIdentifier);
        $command->shouldReceive('getUser')
            ->andReturn($userId);

        $result = $this->sut->handleCommand($command);

        $expectedMessages = [
            'Error on line 2: expected 3 items in row, found 4',
            'Error on line 3: expected 3 items in row, found 2',
            'Licence 100 result message 1',
            'Licence 100 result message 2',
            'Licence 101 result message 1',
            'Processing completed successfully',
        ];

        $this->assertEquals($expectedMessages, $result->getMessages());
    }

    public function testFailOnTooManyRows(): void
    {
        $documentIdentifier = 'documentIdentifier2';

        // create a csv with 5001 lines
        $fileContents = implode("\n", array_fill(0, 5001, '1,4,8'));

        $file = m::mock(ContentStoreFile::class);
        $file->shouldReceive('getContent')
            ->andReturn($fileContents);

        $this->mockFileUploader->shouldReceive('download')
            ->with($documentIdentifier)
            ->andReturn($file);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getDocumentIdentifier')
            ->andReturn($documentIdentifier);

        $result = $this->sut->handleCommand($command);

        $expectedMessages = [
            'Line count of 5001 exceeds permitted maximum of 5000 - file not processed'
        ];

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }
}
