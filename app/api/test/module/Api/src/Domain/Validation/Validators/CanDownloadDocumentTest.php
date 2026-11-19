<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanDownloadDocument;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use LmcRbacMvc\Identity\IdentityInterface;
use Mockery as m;

final class CanDownloadDocumentTest extends AbstractValidatorsTestCase
{
    private const string DOCUMENT_ID = '123';
    private const int USER_ID = 123456;

    public function setUp(): void
    {
        $this->sut = m::mock(CanDownloadDocument::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    public function testExternalUserCanDownloadTheirOrganisationsMessageAttachment(): void
    {
        $this->identity(internal: false, transportManager: false);
        $document = $this->document(attachedToMessage: true);

        $this->mockRepo(Repository\Correspondence::class)->shouldReceive('fetchList')->never();
        $this->setIsValid('isOwner', [$document], true);

        $this->assertTrue($this->sut->isValid(self::DOCUMENT_ID));
    }

    public function testExternalUserCannotDownloadAnotherOrganisationsMessageAttachment(): void
    {
        $this->identity(internal: false, transportManager: false);
        $document = $this->document(attachedToMessage: true);

        $this->setIsValid('isOwner', [$document], false);

        $this->assertFalse($this->sut->isValid(self::DOCUMENT_ID));
    }

    public function testExternalUserCannotDownloadAnUploadThatWasNeverSent(): void
    {
        $this->identity(internal: false, transportManager: false);
        $this->document(attachedToMessage: false);

        $this->mockRepo(Repository\Correspondence::class)
            ->shouldReceive('fetchList')
            ->andReturn(new ArrayCollection([]));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(self::DOCUMENT_ID)
            ->andReturn([]);

        $this->assertFalse($this->sut->isValid(self::DOCUMENT_ID));
    }

    public function testTransportManagerStillOnlyDownloadsDocumentsTheyCreated(): void
    {
        $this->identity(internal: false, transportManager: true);
        $this->document(attachedToMessage: true, createdBy: self::USER_ID + 1);

        $this->assertFalse($this->sut->isValid(self::DOCUMENT_ID));
    }

    public function testInternalUserCanDownload(): void
    {
        $this->identity(internal: true, transportManager: false);

        $this->assertTrue($this->sut->isValid(self::DOCUMENT_ID));
    }

    private function document(bool $attachedToMessage, int $createdBy = self::USER_ID): m\MockInterface
    {
        $document = m::mock(Entity\Doc\Document::class);
        $document->allows('getIsExternal')->andReturn(false);
        $document->allows('getMessagingMessage')
            ->andReturn($attachedToMessage ? m::mock(Entity\Messaging\MessagingMessage::class) : null);
        $document->allows('getCreatedBy->getId')->andReturn($createdBy);
        $document->allows('getCategory->getId')->andReturn(100);
        $document->allows('getSubCategory->getId')->andReturn(1000);
        $document->allows('getDescription')->andReturn('Description');

        $this->mockRepo(Repository\Document::class)->shouldReceive('fetchById')->andReturn($document);

        return $document;
    }

    private function identity(bool $internal, bool $transportManager): void
    {
        $organisation = m::mock(Entity\Organisation\Organisation::class);
        $organisation->allows('getId')->andReturn(567890);

        $identity = m::mock(IdentityInterface::class);
        $identity->allows('getUser->getOrganisationUsers')->andReturn(new ArrayCollection([$organisation]));
        $identity->allows('getUser->getRelatedOrganisation')->andReturn($organisation);
        $identity->allows('getUser->isSystemUser')->andReturn(false);
        $identity->allows('getUser->getId')->andReturn(self::USER_ID);
        $identity->allows('getUser->getLocalAuthority')->andReturn(null);
        $identity->allows('getUser->hasRoles')->andReturn(false)->byDefault();
        $identity->allows('getUser->hasRoles')
            ->with([Entity\User\Role::ROLE_OPERATOR_TM])
            ->andReturn($transportManager);

        $this->auth->allows('isGranted')->with(Permission::TRANSPORT_MANAGER, null)->andReturn($transportManager);
        $this->auth->allows('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn($internal);
        $this->auth->allows('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(!$internal);
        $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)->andReturn(false);
        $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)->andReturn(false);
        $this->auth->allows('getIdentity')->andReturn($identity);
    }
}
