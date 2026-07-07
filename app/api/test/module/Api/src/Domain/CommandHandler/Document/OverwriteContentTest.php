<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\Document\OverwriteContent;
use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\DocumentShare\Service\DocumentStoreInterface;
use Dvsa\Olcs\Transfer\Command\Document\OverwriteContent as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class OverwriteContentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new OverwriteContent();
        $this->mockRepo('Document', Document::class);

        $this->mockedSmServices = [
            'ContentStore' => m::mock(DocumentStoreInterface::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $documentId = 123;
        $identifier = 'test-path';
        $content = 'new file content';

        $command = Cmd::create(['id' => $documentId, 'content' => $content]);

        $document = m::mock(DocumentEntity::class)->makePartial();
        $document->shouldReceive('getIdentifier')->andReturn($identifier);
        $document->shouldReceive('getId')->andReturn($documentId);

        $this->repoMap['Document']->shouldReceive('fetchById')
            ->with($documentId)
            ->once()
            ->andReturn($document);

        $response = m::mock();
        $response->shouldReceive('isSuccess')->andReturn(true);

        $this->mockedSmServices['ContentStore']->shouldReceive('update')
            ->once()
            ->with($identifier, m::type(File::class))
            ->andReturn($response);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => $documentId,
            ],
            'messages' => [
                'Document content overwritten',
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandThrowsOnUpdateFailure(): void
    {
        $documentId = 123;
        $identifier = 'test-path';
        $content = 'new file content';

        $command = Cmd::create(['id' => $documentId, 'content' => $content]);

        $document = m::mock(DocumentEntity::class)->makePartial();
        $document->shouldReceive('getIdentifier')->andReturn($identifier);

        $this->repoMap['Document']->shouldReceive('fetchById')
            ->with($documentId)
            ->once()
            ->andReturn($document);

        $response = m::mock();
        $response->shouldReceive('isSuccess')->andReturn(false);

        $this->mockedSmServices['ContentStore']->shouldReceive('update')
            ->once()
            ->with($identifier, m::type(File::class))
            ->andReturn($response);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to overwrite document content for identifier: test-path');

        $this->sut->handleCommand($command);
    }
}
