<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Domain\Repository\CorrespondenceInbox;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\DeleteDocument;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\DeleteSubmission as DeleteSubmissionCmd;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox as CorrespondenceInboxEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as Entity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate as SlaTargetDateEntity;
use Dvsa\Olcs\Api\Domain\Repository\SlaTargetDate;

/**
 * Delete Document Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteDocumentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteDocument();
        $this->mockRepo('Document', Document::class);
        $this->mockRepo('CorrespondenceInbox', CorrespondenceInbox::class);
        $this->mockRepo('SlaTargetDate', SlaTargetDate::class);

        $this->mockedSmServices = [
            'FileUploader' => m::mock()
        ];

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    /**
     * Tests handleCommand
     */
    public function testHandleCommand()
    {
        $documentId = 123;
        $command = Cmd::create(['id' => $documentId]);

        /** @var Entity $document */
        $document = m::mock(Entity::class)->makePartial();
        $document->setIdentifier('ABC');
        $document->setId($documentId);

        $this->mockedSmServices['FileUploader']->shouldReceive('remove')
            ->once()
            ->with('ABC');

        $this->repoMap['Document']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($document)
            ->shouldReceive('delete')
            ->with($document);

        $correspondenceInbox1 = m::mock(CorrespondenceInboxEntity::class);
        $correspondenceInbox2 = m::mock(CorrespondenceInboxEntity::class);
        $correspondenceInboxes = [$correspondenceInbox1, $correspondenceInbox2];
        $this->repoMap['CorrespondenceInbox']->shouldReceive('fetchByDocumentId')
            ->with($documentId)
            ->andReturn($correspondenceInboxes)
            ->once()
            ->shouldReceive('delete')
            ->with($correspondenceInbox1)
            ->once()
            ->shouldReceive('delete')
            ->with($correspondenceInbox2)
            ->once();

        $slaTargetDate1 = m::mock(SlaTargetDateEntity::class);
        $slaTargetDate2 = m::mock(SlaTargetDateEntity::class);
        $slaTargetDates = [$slaTargetDate1, $slaTargetDate2];
        $this->repoMap['SlaTargetDate']->shouldReceive('fetchByDocumentId')
            ->with($documentId)
            ->once()
            ->andReturn($slaTargetDates)
            ->shouldReceive('delete')
            ->once()
            ->with($slaTargetDate1)
            ->shouldReceive('delete')
            ->with($slaTargetDate2)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'File removed',
                'Document deleted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests handleCommand calls the extra side effect if the document is ebsr pack
     */
    public function testHandleCommandEbsrDoc()
    {
        $ebsrSubId = 123345;
        $documentId = 123;
        $command = Cmd::create(['id' => 123]);

        /** @var EbsrSubmissionEntity $ebsrSubmission */
        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class)->makePartial();
        $ebsrSubmission->setId($ebsrSubId);

        /** @var Entity $document */
        $document = m::mock(Entity::class)->makePartial();
        $document->setIdentifier('ABC');
        $document->shouldReceive('getEbsrSubmission')->andReturn($ebsrSubmission);
        $document->setId($documentId);

        $this->mockedSmServices['FileUploader']->shouldReceive('remove')
            ->once()
            ->with('ABC');

        $this->repoMap['Document']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($document)
            ->shouldReceive('delete')
            ->with($document);

        $this->repoMap['CorrespondenceInbox']->shouldReceive('fetchByDocumentId')
            ->with($documentId)
            ->andReturn([])
            ->once();

        $this->repoMap['SlaTargetDate']->shouldReceive('fetchByDocumentId')
            ->with($documentId)
            ->andReturn([])
            ->once();

        $this->expectedSideEffect(DeleteSubmissionCmd::class, ['id' => $ebsrSubId], new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'File removed',
                'Document deleted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
