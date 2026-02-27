<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Doc\DocumentToDelete as DocumentToDeleteEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\RemoveDeletedDocuments;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Document\RemoveDeletedDocuments as Cmd;
use Laminas\Http\Response;

/**
 * RemoveDeletedDocumentsTest
 */
class RemoveDeletedDocumentsTest extends AbstractCommandHandlerTestCase
{
    public $mockUploader;
    public function setUp(): void
    {
        $this->sut = new RemoveDeletedDocuments();
        $this->mockRepo('DocumentToDelete', Repository\DocumentToDelete::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);
        $this->mockRepo('Queue', Repository\Queue::class);

        $this->mockUploader = m::mock(ContentStoreFileUploader::class);
        $this->mockedSmServices['FileUploader'] = $this->mockUploader;

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create([]);

        $this->mockUploader->shouldReceive('remove')->with('doc1.rtf')->once()->andReturn(
            m::mock(Response::class)->shouldReceive('isOk')->with()->once()->andReturn(true)->getMock()
        );
        $this->mockUploader->shouldReceive('remove')->with('doc2.rtf')->once()->andReturn(
            m::mock(Response::class)
                ->shouldReceive('isOk')->with()->once()->andReturn(false)
                ->shouldReceive('isNotFound')->with()->once()->andReturn(true)
                ->getMock()
        );
        $this->mockUploader->shouldReceive('remove')->with('doc3.rtf')->once()->andReturn(
            m::mock(Response::class)
                ->shouldReceive('isOk')->with()->once()->andReturn(false)
                ->shouldReceive('isNotFound')->with()->once()->andReturn(false)
                ->shouldReceive('getStatusCode')->with()->once()->andReturn(500)
                ->getMock()
        );
        $this->mockUploader->shouldReceive('remove')->with('doc4.rtf')->once()->andReturn(
            m::mock(Response::class)->shouldReceive('isOk')->with()->once()->andReturn(true)->getMock()
        );

        $documentsToDelete = [
            (new DocumentToDeleteEntity())->setDocumentStoreId('doc4.rtf'),
            (new DocumentToDeleteEntity())->setDocumentStoreId('doc3.rtf'),
            (new DocumentToDeleteEntity())->setDocumentStoreId('doc2.rtf'),
            (new DocumentToDeleteEntity())->setDocumentStoreId('doc1.rtf'),
        ];

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisableDataRetentionDocumentDelete')->with()->once()->andReturn(false);

        $this->repoMap['DocumentToDelete']
            ->shouldReceive('fetchListOfDocumentToDelete')->with(100)->once()->andReturn($documentsToDelete)
            ->shouldReceive('delete')->with($documentsToDelete[0])->once()
            ->shouldReceive('delete')->with($documentsToDelete[2])->once()
            ->shouldReceive('delete')->with($documentsToDelete[3])->once()
            ->shouldReceive('save')->with($documentsToDelete[1])->once()
            ->shouldReceive('fetchListOfDocumentToDeleteIncludingPostponed')->with(1)->once()->andReturn([]);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Document delete failed. DocumnetStoreId = \'doc3.rtf\', code = 500',
                'Remove documents : 2 success, 1 not found, 1 errors',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandQueueItemCreated(): void
    {
        $command = Cmd::create([]);

        $documentsToDelete = [
            (new DocumentToDeleteEntity())->setDocumentStoreId('doc1.rtf'),
        ];

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisableDataRetentionDocumentDelete')->with()->once()->andReturn(false);

        $this->repoMap['DocumentToDelete']
            ->shouldReceive('fetchListOfDocumentToDelete')->with(100)->once()->andReturn([])
            ->shouldReceive('fetchListOfDocumentToDeleteIncludingPostponed')->with(1)->once()->andReturn($documentsToDelete);

        $this->repoMap['Queue']
            ->shouldReceive('fetchNextItemIncludingPostponed')->with([Queue::TYPE_REMOVE_DELETED_DOCUMENTS])->once()->andReturn(null);

        $this->expectedSideEffect(
            Create::class,
            ['type' => Queue::TYPE_REMOVE_DELETED_DOCUMENTS, 'status' => Queue::STATUS_QUEUED],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Remove documents : 0 success, 0 not found, 0 errors',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNoDocumentsToDelete(): void
    {
        $command = Cmd::create([]);

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisableDataRetentionDocumentDelete')->with()->once()->andReturn(false);

        $this->repoMap['DocumentToDelete']
            ->shouldReceive('fetchListOfDocumentToDelete')->with(100)->once()->andReturn([])
            ->shouldReceive('fetchListOfDocumentToDeleteIncludingPostponed')->with(1)->once()->andReturn([]);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Remove documents : 0 success, 0 not found, 0 errors',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandQueueItemCreatedWhenDocumentPostponed(): void
    {
        $command = Cmd::create([]);

        $documentToDelete = (new DocumentToDeleteEntity());
        $documentToDelete->setDocumentStoreId('doc1.rtf');
        $documentToDelete->setProcessAfterDate((new \DateTime())->add(new \DateInterval('P2D')));

        $documentsToDelete = [
            (new DocumentToDeleteEntity())->setDocumentStoreId('doc1.rtf'),
        ];

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisableDataRetentionDocumentDelete')->with()->once()->andReturn(false);

        $this->repoMap['DocumentToDelete']
            ->shouldReceive('fetchListOfDocumentToDelete')->with(100)->once()->andReturn([])
            ->shouldReceive('fetchListOfDocumentToDeleteIncludingPostponed')->with(1)->once()->andReturn($documentsToDelete);

        $this->repoMap['Queue']
            ->shouldReceive('fetchNextItemIncludingPostponed')->with([Queue::TYPE_REMOVE_DELETED_DOCUMENTS])->once()->andReturn(null);

        $this->expectedSideEffect(
            Create::class,
            ['type' => Queue::TYPE_REMOVE_DELETED_DOCUMENTS, 'status' => Queue::STATUS_QUEUED],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Remove documents : 0 success, 0 not found, 0 errors',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandQueueItemCreatedWhenDocumentAndQueueItemPostponed(): void
    {
        $command = Cmd::create([]);

        $documentToDelete = (new DocumentToDeleteEntity());
        $documentToDelete->setDocumentStoreId('doc1.rtf');
        $documentToDelete->setProcessAfterDate((new \DateTime())->add(new \DateInterval('P2D')));

        $documentsToDelete = [$documentToDelete];

        $nextQueueItem = new Queue();
        $nextQueueItem->setProcessAfterDate((new \DateTime())->add(new \DateInterval('P4D')));

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisableDataRetentionDocumentDelete')->with()->once()->andReturn(false);

        $this->repoMap['DocumentToDelete']
            ->shouldReceive('fetchListOfDocumentToDelete')->with(100)->once()->andReturn([])
            ->shouldReceive('fetchListOfDocumentToDeleteIncludingPostponed')->with(1)->once()->andReturn($documentsToDelete);

        $this->repoMap['Queue']
            ->shouldReceive('fetchNextItemIncludingPostponed')->with([Queue::TYPE_REMOVE_DELETED_DOCUMENTS])->once()->andReturn($nextQueueItem);

        $this->expectedSideEffect(
            Create::class,
            ['type' => Queue::TYPE_REMOVE_DELETED_DOCUMENTS, 'status' => Queue::STATUS_QUEUED],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Remove documents : 0 success, 0 not found, 0 errors',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandQueueItemNotCreatedWhenQueueItemPostponedBeforeDocument(): void
    {
        $command = Cmd::create([]);

        $documentToDelete = (new DocumentToDeleteEntity());
        $documentToDelete->setDocumentStoreId('doc1.rtf');
        $documentToDelete->setProcessAfterDate((new \DateTime())->add(new \DateInterval('P3D')));

        $documentsToDelete = [$documentToDelete];

        $nextQueueItem = new Queue();
        $nextQueueItem->setProcessAfterDate((new \DateTime())->add(new \DateInterval('P1D')));

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisableDataRetentionDocumentDelete')->with()->once()->andReturn(false);

        $this->repoMap['DocumentToDelete']
            ->shouldReceive('fetchListOfDocumentToDelete')->with(100)->once()->andReturn([])
            ->shouldReceive('fetchListOfDocumentToDeleteIncludingPostponed')->with(1)->once()->andReturn($documentsToDelete);

        $this->repoMap['Queue']
            ->shouldReceive('fetchNextItemIncludingPostponed')->with([Queue::TYPE_REMOVE_DELETED_DOCUMENTS])->once()->andReturn($nextQueueItem);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Remove documents : 0 success, 0 not found, 0 errors',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandDisabled(): void
    {
        $command = Cmd::create([]);

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisableDataRetentionDocumentDelete')->with()->once()->andReturn(true);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Removing deleted documents is disabled by system parameter',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
