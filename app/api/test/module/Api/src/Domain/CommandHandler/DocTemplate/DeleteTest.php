<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DocTemplate;

use Dvsa\Olcs\Api\Domain\CommandHandler\DocTemplate\Delete;
use Dvsa\Olcs\Api\Domain\Repository\DocTemplate;
use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate as DocTemplateEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Delete DocumentTemplate Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Delete();
        $this->mockRepo('DocTemplate', DocTemplate::class);
        $this->mockRepo('Document', Document::class);

        $this->mockedSmServices = [
            'FileUploader' => m::mock()
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $docTemplateId = 123;
        $command = Cmd::create(['id' => $docTemplateId]);

        /** @var DocumentEntity $document */
        $document = m::mock(DocumentEntity::class)->makePartial();
        $document->setIdentifier('DocIdentifier');

        /** @var DocTemplateEntity $docTemplate */
        $docTemplate = m::mock(DocTemplateEntity::class)->makePartial();
        $docTemplate->setDocument($document);
        $docTemplate->setId($docTemplateId);

        $this->mockedSmServices['FileUploader']->shouldReceive('remove')
            ->once()
            ->with('DocIdentifier');

        $this->repoMap['DocTemplate']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($docTemplate)
            ->shouldReceive('delete')
            ->once()
            ->with($docTemplate);

        $this->repoMap['Document']
            ->shouldReceive('delete')
            ->once()
            ->with($document);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'File removed',
                'Document record deleted',
                'DocTemplate record deleted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandEmptyIdentifier(): void
    {
        $docTemplateId = 123;
        $command = Cmd::create(['id' => $docTemplateId]);

        /** @var DocumentEntity $document */
        $document = m::mock(DocumentEntity::class)->makePartial();
        $document->setIdentifier(null);

        /** @var DocTemplateEntity $docTemplate */
        $docTemplate = m::mock(DocTemplateEntity::class)->makePartial();
        $docTemplate->setDocument($document);
        $docTemplate->setId($docTemplateId);

        $this->repoMap['DocTemplate']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($docTemplate)
            ->shouldReceive('delete')
            ->once()
            ->with($docTemplate);

        $this->repoMap['Document']
            ->shouldReceive('delete')
            ->once()
            ->with($document);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Document record deleted',
                'DocTemplate record deleted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
