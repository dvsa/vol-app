<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Domain\Repository\CorrespondenceInbox;
use LmcRbacMvc\Service\AuthorizationService;
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
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Doctrine\Common\Collections\ArrayCollection;

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
            'FileUploader' => m::mock(),
            AuthorizationService::class => m::mock(AuthorizationService::class)
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
        $command = Cmd::create(['id' => $documentId, 'unlinkLicence' => false]);

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
        $command = Cmd::create(['id' => 123, 'unlinkLicence' => false]);

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

    /**
     * Tests handleCommand with unlinkLicence set to true
     */
    public function testHandleCommandWithUnlinkLicence()
    {
        $documentId = 123;
        $userId = 456;
        $command = Cmd::create(['id' => $documentId, 'unlinkLicence' => true]);

        $user = m::mock(User::class);
        $user->shouldReceive('getId')->andReturn($userId);

        $createdByUser = m::mock(User::class);
        $createdByUser->shouldReceive('getId')->andReturn($userId);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $document = m::mock(Entity::class)->makePartial();
        $document->setIdentifier('ABC');
        $document->setId($documentId);
        $document->shouldReceive('getCreatedBy')->andReturn($createdByUser);
        $document->shouldReceive('setLicence')->with(null)->once();
        $document->shouldReceive('setApplication')->with(null)->once();

        $this->mockedSmServices['FileUploader']->shouldReceive('remove')
            ->once()
            ->with('ABC');

        $this->repoMap['Document']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($document)
            ->shouldReceive('save')
            ->with($document)
            ->once()
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
     * Tests handleCommand with unlinkLicence set to true but different user
     */
    public function testHandleCommandWithUnlinkLicenceDifferentUser()
    {
        $documentId = 123;
        $currentUserId = 456;
        $createdByUserId = 789; // Different user ID
        $command = Cmd::create(['id' => $documentId, 'unlinkLicence' => true]);

        $user = m::mock(User::class);
        $user->shouldReceive('getId')->andReturn($currentUserId);

        $createdByUser = m::mock(User::class);
        $createdByUser->shouldReceive('getId')->andReturn($createdByUserId);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $document = m::mock(Entity::class)->makePartial();
        $document->setIdentifier('ABC');
        $document->setId($documentId);
        $document->shouldReceive('getCreatedBy')->andReturn($createdByUser);

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
