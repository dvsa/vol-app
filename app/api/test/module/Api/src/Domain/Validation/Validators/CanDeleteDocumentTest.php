<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanDeleteDocument;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Domain\Repository;
use Exception;
use LmcRbacMvc\Identity\IdentityInterface;
use Mockery as m;

class CanDeleteDocumentTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanDeleteDocument
     */
    protected $sut;

    private const IS_SYSTEM_USER = 0;
    private const IS_INTERNAL_USER = 1;
    private const IS_EXTERNAL_USER = 2;
    private const DOCUMENT_ID = "123";

    public function setUp(): void
    {
        $this->sut = new CanDeleteDocument();

        parent::setUp();
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testSystemUserCanDeleteDocument(): void
    {
        $this->setupMockIdentity(static::IS_SYSTEM_USER, $this->getMockOrganisation());
        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->with(static::DOCUMENT_ID)
            ->andReturn($this->getMockDocument(true));


        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testInternalUserCanDeleteDocument(): void
    {
        $this->setupMockIdentity(static::IS_INTERNAL_USER, $this->getMockOrganisation());
        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->with(static::DOCUMENT_ID)
            ->andReturn($this->getMockDocument(true));

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testExternalUserCanDeleteDocumentWhenThereIsNoApplicationAttached(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER, $this->getMockOrganisation());
        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->with(static::DOCUMENT_ID)
            ->andReturn($document = $this->getMockDocument(true));

        $this->setIsValid('isOwner', [$document], true);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testIsExternalUserCanDeleteDocWhenAppAttachedButNotSubmitted(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER, $this->getMockOrganisation());
        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->with(static::DOCUMENT_ID)
            ->andReturn($document = $this->getMockDocument(true, relatedApplication: $this->getMockApplication()));

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testIsExternalUserCannotDeleteDocWithAppAttachedAndIsSubmitted(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER, $this->getMockOrganisation());
        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->with(static::DOCUMENT_ID)
            ->andReturn($this->getMockDocument(true, relatedApplication: $this->getMockApplication(true)));

        $this->assertFalse($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws Exception
     * @return m\MockInterface The mocked document object.
     */
    private function getMockDocument(
        bool $isExternal,
        int $createdById = 123456,
        m\MockInterface $relatedApplication = null,
        m\MockInterface $relatedOrganisation = null
    ): m\MockInterface {
        if ($relatedOrganisation === null) {
            $relatedOrganisation = m::mock(Entity\Organisation\Organisation::class);
            $relatedOrganisation->allows('getId')->andReturn(567890);
        }

        $mockDoc = m::mock(Entity\Doc\Document::class);
        $mockDoc->allows('getIsExternal')->andReturn($isExternal);
        $mockDoc->allows('getId')->andReturn(static::DOCUMENT_ID);
        $mockDoc->allows('getCreatedBy->getId')->andReturn($createdById);
        $mockDoc->allows('getRelatedOrganisation')->andReturn($relatedOrganisation);
        $mockDoc->allows('getApplication')->andReturn($relatedApplication);

        return $mockDoc;
    }

    private function getMockOrganisation(): m\MockInterface
    {
        $mockOrganisation = m::mock(Entity\Organisation\Organisation::class);
        $mockOrganisation->allows('getId')->andReturn(567890);

        return $mockOrganisation;
    }

    private function getMockApplication(bool $isSubmitted = false): m\MockInterface
    {
        $mockApplication = m::mock(Entity\Application\Application::class);
        $mockApplication->allows('getId')->andReturn(567890);
        $mockApplication->allows('getStatus->getId')->andReturn($isSubmitted ? Entity\Application\Application::APPLICATION_STATUS_VALID : Entity\Application\Application::APPLICATION_STATUS_NOT_SUBMITTED);

        return $mockApplication;
    }


    /**
     * @throws NotFoundException
     * @throws Exception
     */
    private function setupMockIdentity(int $userType, m\MockInterface $organisation = null, int $userId = 123456): void
    {
        $mockIdentity = m::mock(IdentityInterface::class);
        if ($organisation === null) {
            $mockIdentity->allows('getUser->getOrganisationUsers')->andReturn(new ArrayCollection([]));
            $mockIdentity->allows('getUser->getRelatedOrganisation')->andReturn(null);
        } else {
            $mockIdentity->allows('getUser->getOrganisationUsers')->andReturn(new ArrayCollection([$organisation]));
            $mockIdentity->allows('getUser->getRelatedOrganisation')->andReturn($organisation);
        }

        $mockIdentity->allows('getUser->isSystemUser')->andReturn($userType === static::IS_SYSTEM_USER);
        $mockIdentity->allows('getUser->getId')->andReturn($userId);

        switch ($userType) {
            case static::IS_SYSTEM_USER:
                $mockIdentity->allows('getUser->hasRoles')->andReturn(false)->byDefault();
                $this->auth->allows('isGranted')->with(Permission::TRANSPORT_MANAGER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)->andReturn(false);
                break;
            case static::IS_INTERNAL_USER:
                $mockIdentity->allows('getUser->hasRoles')->andReturn(false)->byDefault();
                $this->auth->allows('isGranted')->with(Permission::TRANSPORT_MANAGER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(true);
                $this->auth->allows('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)->andReturn(false);
                break;
            case static::IS_EXTERNAL_USER:
                $mockIdentity->allows('getUser->hasRoles')->andReturn(false)->byDefault();
                $this->auth->allows('isGranted')->with(Permission::TRANSPORT_MANAGER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)->andReturn(false);
                break;
            default:
                /** @noinspection PhpUnhandledExceptionInspection */
                throw new Exception("Unexpected user type provided");
        }

        $this->auth->allows('getIdentity')->andReturn($mockIdentity);
    }
}
