<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessDocument;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer;
use Exception;
use LmcRbacMvc\Identity\IdentityInterface;
use Mockery as m;
use Mockery\MockInterface;

class CanAccessDocumentTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanAccessDocument
     */
    protected $sut;

    private const int IS_SYSTEM_USER = 0;
    private const int IS_INTERNAL_USER = 1;
    private const int IS_EXTERNAL_USER = 2;
    private const int IS_LOCAL_AUTHORITY_USER = 3;
    private const int IS_LOCAL_AUTHORITY_ADMIN = 4;
    private const int IS_TRANSPORT_MANAGER_USER = 5;
    private const string DOCUMENT_ID = "123";

    public function setUp(): void
    {
        $this->sut = m::mock(CanAccessDocument::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testSystemUserCanAccessDocument(): void
    {
        $this->setupMockIdentity(static::IS_SYSTEM_USER);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testInternalUserCanAccessDocument(): void
    {
        $this->setupMockIdentity(static::IS_INTERNAL_USER);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testExternalUserCanAccessDocumentIsExternalDocument(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER);

        $this->mockRepo(\Dvsa\Olcs\Api\Domain\Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(true));

        $this->setIsValid('isOwner', [$document], true);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testExternalUserCanAccessDocumentNotExternalDocumentIsCorrespondence(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER, $org = $this->getMockOrganisation());

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(false));
        $this->mockRepo(Repository\Correspondence::class)
            ->shouldReceive('fetchList')
            ->withArgs(function ($query) use ($org) {
                $this->assertInstanceOf(Transfer\Query\QueryInterface::class, $query);
                $this->assertInstanceOf(Transfer\Query\Correspondence\Correspondences::class, $query);
                /** @var Transfer\Query\Correspondence\Correspondences $query */
                return $query->getOrganisation() === $org->getId();
            })
            ->andReturn(new ArrayCollection([
                ['document' => ['id' => static::DOCUMENT_ID]],
            ]));

        $this->setIsValid('isOwner', [$document], true);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testExternalUserCanAccessDocumentNotExternalDocumentNotCorrespondenceIsTxcDocument(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER, $org = $this->getMockOrganisation());

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(false));
        $this->mockRepo(Repository\Correspondence::class)
            ->shouldReceive('fetchList')
            ->withArgs(function ($query) use ($org) {
                $this->assertInstanceOf(Transfer\Query\QueryInterface::class, $query);
                $this->assertInstanceOf(Transfer\Query\Correspondence\Correspondences::class, $query);
                /** @var Transfer\Query\Correspondence\Correspondences $query */
                return $query->getOrganisation() === $org->getId();
            })
            ->andReturn(new ArrayCollection([]));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturn([
                [m::mock(Entity\Ebsr\TxcInbox::class)]
            ]);

        $this->setIsValid('isOwner', [$document], true);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testExternalUserCannotAccessDocumentNotExternalDocumentNotCorrespondenceNotTxcDocument(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER, $org = $this->getMockOrganisation());

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($this->getMockDocument(false));
        $this->mockRepo(Repository\Correspondence::class)
            ->shouldReceive('fetchList')
            ->withArgs(function ($query) use ($org) {
                $this->assertInstanceOf(Transfer\Query\QueryInterface::class, $query);
                $this->assertInstanceOf(Transfer\Query\Correspondence\Correspondences::class, $query);
                /** @var Transfer\Query\Correspondence\Correspondences $query */
                return $query->getOrganisation() === $org->getId();
            })
            ->andReturn(new ArrayCollection([]));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturn([]);

        $this->assertFalse($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testExternalUserWithNoOrgCannotAccessDocumentNotExternalDocumentNotCorrespondenceAsNoOrg(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER);

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(false));
        $this->mockRepo(Repository\Correspondence::class)
            ->shouldReceive('fetchList')
            ->never()
            ->andReturn(new ArrayCollection([]));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturn([]);

        $this->assertFalse($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testTransportManagerCanAccessDocument(): void
    {
        $this->setupMockIdentity(static::IS_TRANSPORT_MANAGER_USER, null, $userId = 437924);

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(true, $userId));

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testTransportManagerCannotAccessDocumentIfNotCreatedBySameUser(): void
    {
        $this->setupMockIdentity(static::IS_TRANSPORT_MANAGER_USER, null, 9000);

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(true, 8999));

        $this->assertFalse($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     *
     * @throws NotFoundException
     * @throws Exception
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('localAuthorityTypeProvider')]
    public function testLocalAuthorityUserCanAccessTxcDocumentForTheirAuthority(int $localAuthorityUserType): void
    {
        $mockLocalAuthority = m::mock(Entity\Bus\LocalAuthority::class);
        $mockLocalAuthority
            ->allows('getId')
            ->andReturn(780245);

        $this->setupMockIdentity($localAuthorityUserType, $org = $this->getMockOrganisation(), 123456, $mockLocalAuthority);

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(false));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturn([
                m::mock(Entity\Ebsr\TxcInbox::class)
                ->shouldReceive('getLocalAuthority')
                ->andReturn($mockLocalAuthority)
                ->getMock()
            ]);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     *
     * @throws NotFoundException
     * @throws Exception
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('localAuthorityTypeProvider')]
    public function testLocalAuthorityUserCanAccessTxcDocumentForTheirAuthorityMultipleTxcRecordsMatchingCurrentAuthority(int $localAuthorityUserType): void
    {
        $mockLocalAuthority = m::mock(Entity\Bus\LocalAuthority::class);
        $mockLocalAuthority
            ->allows('getId')
            ->andReturn(780245);

        $this->setupMockIdentity($localAuthorityUserType, $org = $this->getMockOrganisation(), 123456, $mockLocalAuthority);

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(false));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturn([
                m::mock(Entity\Ebsr\TxcInbox::class)
                    ->shouldReceive('getLocalAuthority')
                    ->andReturn($mockLocalAuthority)
                    ->getMock(),
                m::mock(Entity\Ebsr\TxcInbox::class)
                    ->shouldReceive('getLocalAuthority')
                    ->andReturn($mockLocalAuthority)
                    ->getMock(),
            ]);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     *
     * @throws NotFoundException
     * @throws Exception
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('localAuthorityTypeProvider')]
    public function testLocalAuthorityUserCanAccessTxcDocumentForTheirAuthorityMultipleTxcRecordsOneMatchingCurrentAuthorityAnotherNoAuthority(int $localAuthorityUserType): void
    {
        $mockLocalAuthority = m::mock(Entity\Bus\LocalAuthority::class);
        $mockLocalAuthority
            ->allows('getId')
            ->andReturn(780245);

        $this->setupMockIdentity($localAuthorityUserType, $org = $this->getMockOrganisation(), 123456, $mockLocalAuthority);

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(false));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturn([
                m::mock(Entity\Ebsr\TxcInbox::class)
                    ->shouldReceive('getLocalAuthority')
                    ->andReturn($mockLocalAuthority)
                    ->getMock(),
                m::mock(Entity\Ebsr\TxcInbox::class)
                    ->shouldReceive('getLocalAuthority')
                    ->andReturn(null)
                    ->getMock(),
            ]);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     *
     * @throws NotFoundException
     * @throws Exception
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('localAuthorityTypeProvider')]
    public function testLocalAuthorityUserCannotAccessTxcDocumentForDifferentAuthority(int $localAuthorityUserType): void
    {
        $mockLocalAuthorityA = m::mock(Entity\Bus\LocalAuthority::class);
        $mockLocalAuthorityA
            ->allows('getId')
            ->andReturn(780245);

        $mockLocalAuthorityB = m::mock(Entity\Bus\LocalAuthority::class);
        $mockLocalAuthorityB
            ->allows('getId')
            ->andReturn(824578);

        $this->setupMockIdentity($localAuthorityUserType, $org = $this->getMockOrganisation(), 123456, $mockLocalAuthorityA);

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(false));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturn([
                m::mock(Entity\Ebsr\TxcInbox::class)
                    ->shouldReceive('getLocalAuthority')
                    ->andReturn($mockLocalAuthorityB)
                    ->getMock()
            ]);

        $this->assertFalse($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     *
     * @throws NotFoundException
     * @throws Exception
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('localAuthorityTypeProvider')]
    public function testLocalAuthorityUserCannotAccessTxcDocumentForDocumentWithNoAuthority(int $localAuthorityUserType): void
    {
        $mockLocalAuthority = m::mock(Entity\Bus\LocalAuthority::class);
        $mockLocalAuthority
            ->allows('getId')
            ->andReturn(780245);

        $this->setupMockIdentity($localAuthorityUserType, $org = $this->getMockOrganisation(), 123456, $mockLocalAuthority);

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(false));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturn([
                m::mock(Entity\Ebsr\TxcInbox::class)
                    ->shouldReceive('getLocalAuthority')
                    ->andReturn(null)
                    ->getMock()
            ]);

        $this->assertFalse($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     *
     * @throws NotFoundException
     * @throws Exception
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('localAuthorityTypeProvider')]
    public function testLocalAuthorityUserCannotAccessDocumentIfNoTxcInboxRecordsFoundForDocumentId(int $localAuthorityUserType): void
    {
        $mockLocalAuthority = m::mock(Entity\Bus\LocalAuthority::class);
        $mockLocalAuthority
            ->allows('getId')
            ->andReturn(780245);

        $this->setupMockIdentity($localAuthorityUserType, $org = $this->getMockOrganisation(), 123456, $mockLocalAuthority);

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(false));
        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturn([]);

        $this->assertFalse($this->sut->isValid(static::DOCUMENT_ID));
    }

    /**
     * @throws Exception
     * @return m\MockInterface The mocked document object.
     */
    private function getMockDocument(
        bool $isExternal,
        int $createdById = 123456,
        m\MockInterface $relatedOrganisation = null,
        int $categoryId = 100,
        int $subCategoryId = 1000,
        string $description = 'Description'
    ): m\MockInterface {
        if ($relatedOrganisation === null) {
            $relatedOrganisation = m::mock(Entity\Organisation\Organisation::class);
            $relatedOrganisation->allows('getId')->andReturn(567890);
        }

        $mockDoc = m::mock(Entity\Doc\Document::class);
        $mockCategory = m::mock(Entity\System\Category::class);
        $mockSubCategory = m::mock(Entity\System\SubCategory::class);

        // Set returns for category and subcategory if provided
        if ($categoryId !== null) {
            $mockCategory->allows('getId')->andReturn($categoryId);
        }
        if ($subCategoryId !== null) {
            $mockSubCategory->allows('getId')->andReturn($subCategoryId);
        }

        $mockDoc->allows('getIsExternal')->andReturn($isExternal);
        $mockDoc->allows('getId')->andReturn(static::DOCUMENT_ID);
        $mockDoc->allows('getCreatedBy->getId')->andReturn($createdById);
        $mockDoc->allows('getRelatedOrganisation')->andReturn($relatedOrganisation);
        $mockDoc->allows('getCategory')->andReturn($mockCategory);
        $mockDoc->allows('getSubCategory')->andReturn($mockSubCategory);
        $mockDoc->allows('getDescription')->andReturn($description);

        return $mockDoc;
    }


    /**
     * @throws NotFoundException
     * @throws Exception
     */
    private function setupMockIdentity(int $userType, m\MockInterface $organisation = null, int $userId = 123456, m\MockInterface $localAuthority = null): void
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

        if ($userType === static::IS_LOCAL_AUTHORITY_USER || $userType === static::IS_LOCAL_AUTHORITY_ADMIN) {
            $mockIdentity->allows('getUser->getLocalAuthority')->andReturn($localAuthority);
        } else {
            $mockIdentity->allows('getUser->getLocalAuthority')->andReturn(null);
        }

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
            case static::IS_LOCAL_AUTHORITY_USER:
                $mockIdentity->allows('getUser->hasRoles')->andReturn(false)->byDefault();
                $mockIdentity->allows('getUser->hasRoles')->with([Entity\User\Role::ROLE_LOCAL_AUTHORITY_USER, Entity\User\Role::ROLE_LOCAL_AUTHORITY_ADMIN])->andReturn(true);
                $this->auth->allows('isGranted')->with(Permission::TRANSPORT_MANAGER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)->andReturn(true);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)->andReturn(false);
                break;
            case static::IS_LOCAL_AUTHORITY_ADMIN:
                $mockIdentity->allows('getUser->hasRoles')->andReturn(false)->byDefault();
                $mockIdentity->allows('getUser->hasRoles')->with([Entity\User\Role::ROLE_LOCAL_AUTHORITY_USER, Entity\User\Role::ROLE_LOCAL_AUTHORITY_ADMIN])->andReturn(true);
                $this->auth->allows('isGranted')->with(Permission::TRANSPORT_MANAGER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_USER, null)->andReturn(false);
                $this->auth->allows('isGranted')->with(Permission::LOCAL_AUTHORITY_ADMIN, null)->andReturn(true);
                break;
            case static::IS_TRANSPORT_MANAGER_USER:
                $mockIdentity->allows('getUser->hasRoles')->andReturn(false)->byDefault();
                $mockIdentity->allows('getUser->hasRoles')->with([Entity\User\Role::ROLE_OPERATOR_TM])->andReturn(true);
                $this->auth->allows('isGranted')->with(Permission::TRANSPORT_MANAGER, null)->andReturn(true);
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

    /**
     * @return MockInterface|Entity\Organisation\Organisation
     */
    private function getMockOrganisation(): m\MockInterface
    {
        $mockOrganisation = m::mock(Entity\Organisation\Organisation::class);
        $mockOrganisation->allows('getId')->andReturn(567890);

        return $mockOrganisation;
    }

    public static function localAuthorityTypeProvider(): array
    {
        return [
            'LOCAL AUTHORITY USER' => [static::IS_LOCAL_AUTHORITY_USER],
            'LOCAL AUTHORITY ADMIN' => [static::IS_LOCAL_AUTHORITY_ADMIN],
        ];
    }

    public function testIsLicencePrintDocumentGV(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER);

        $this->mockRepo(\Dvsa\Olcs\Api\Domain\Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(
                isExternal: false,
                categoryId: Entity\System\Category::CATEGORY_LICENSING,
                subCategoryId: Entity\System\SubCategory::DOC_SUB_CATEGORY_LICENCING_OTHER_DOCUMENTS,
                description: 'GV Licence'
            ));

        $this->setIsValid('isOwner', [$document], true);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    public function testIsLicencePrintDocumentPSV(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER);

        $this->mockRepo(\Dvsa\Olcs\Api\Domain\Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(
                isExternal: false,
                categoryId: Entity\System\Category::CATEGORY_LICENSING,
                subCategoryId: Entity\System\SubCategory::DOC_SUB_CATEGORY_LICENCING_OTHER_DOCUMENTS,
                description: 'PSV Licence'
            ));

        $this->setIsValid('isOwner', [$document], true);

        $this->assertTrue($this->sut->isValid(static::DOCUMENT_ID));
    }

    public function testIsLicencePrintDocumentGVCorrectTitleWrongCategory(): void
    {
        $this->setupMockIdentity(static::IS_EXTERNAL_USER);

        $this->mockRepo(\Dvsa\Olcs\Api\Domain\Repository\Document::class)
            ->shouldReceive('fetchById')
            ->andReturn($document = $this->getMockDocument(
                isExternal: false,
                categoryId: Entity\System\Category::CATEGORY_LICENSING,
                subCategoryId: Entity\System\SubCategory::DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE,
                description: 'GV Licence'
            ));

        $this->mockRepo(Repository\TxcInbox::class)
            ->shouldReceive('fetchLinkedToDocument')
            ->with(static::DOCUMENT_ID)
            ->andReturnFalse();

        $this->setIsValid('isOwner', [$document], true);

        $this->assertFalse($this->sut->isValid(static::DOCUMENT_ID));
    }
}
