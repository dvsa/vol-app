<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueMessage;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\ProposeToRevoke;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument;
use Dvsa\Olcs\Transfer\Command\Licence\ProposeToRevoke as ProposeToRevokeCmd;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Identity\IdentityInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class ProposeToRevokeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProposeToRevoke();
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $mockTeam = m::mock(Team::class);
        $mockTeam->shouldReceive('getId')->andReturn(123);

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('getRoles')->andReturn(new ArrayCollection([]));
        $mockUser->shouldReceive('getId')->andReturn(291);
        $mockUser->shouldReceive('getTeam')->andReturn($mockTeam)->getMock();

        $mockIdentity = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn($mockUser)->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')
            ->andReturn($mockIdentity);

        $contactDetails = m::mock(ContactDetails::class);
        $contactDetails->shouldReceive('getEmailAddress')->andReturn('someEmail@email.com');

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getAdminOrganisationUsers')->andReturn(new ArrayCollection([]));

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getId')->andReturn(7)->getMock();
        $licence->shouldReceive('getTranslateToWelsh')->andReturn(true);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($contactDetails);

        $document = m::mock(Document::class)->shouldReceive('getId')->andReturn(10)->getMock();
        $document->shouldReceive('getDescription')->andReturn('Test Document');

        $this->repoMap['Licence']->shouldReceive('fetchById')->andReturn($licence);
        $this->repoMap['Document']->shouldReceive('fetchById')->andReturn($document);

        $expectedTaskData = [
            'category' => Category::CATEGORY_COMPLIANCE,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
            'description' => 'Check response to PTR',
            'actionDate' => (new DateTime('now'))->add(new \DateInterval('P21D'))->format('Y-m-d'),
            'assignedToUser' => 291,
            'assignedToTeam' => 123,
            'licence' => 7,
            'urgent' => 'Y'
        ];

        $expectedDeleteDocumentData = [
            'id' => 10
        ];

        $expectedAddressData = [
            'template' => 10,
            'licence' => 7,
            'query' => [
                'licence' => 7
            ],
            'category' => Category::CATEGORY_COMPLIANCE,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
            'isExternal' => false,
            'description' => 'Test Document',
            'metadata' => json_encode([
                'details' => [
                    'category' => Category::CATEGORY_COMPLIANCE,
                    'documentSubCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
                    'documentTemplate' => 818,
                    'allowEmail' => true
                ]
            ]),

        ];

        $expectedPrintLettersData = [
            'id' => 100,
            'method' => 'printAndPost'
        ];

        $expectedPrintLettersEmailData = [
            'id' => 100,
            'method' => 'email'
        ];

        $result = new Result();

        $this->expectedSideEffect(CreateQueueMessage::class, [], new Result());
        $this->expectedSideEffect(CreateTask::class, $expectedTaskData, $result);
        $this->expectedSideEffect(DeleteDocument::class, $expectedDeleteDocumentData, $result);

        $result = new Result();
        $result->addId('document', 100, true);
        $result->addId('correspondenceAddress', 102);
        $this->expectedSideEffect(GenerateAndStore::class, $expectedAddressData, $result);
        $this->expectedSideEffect(PrintLetter::class, $expectedPrintLettersData, $result);
        $this->expectedSideEffect(PrintLetter::class, $expectedPrintLettersEmailData, $result);

        $cmd = ProposeToRevokeCmd::create(['licence' => 7, 'document' => 10]);
        $result = $this->sut->handleCommand($cmd);

        $expectedResult = [
            'id' => [
                'document' => 100,
                'correspondenceAddress' => 102
            ],
            'messages' => ['Propose to revoke successfully processed']
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }

    public function testHandleCommandWithEmails(): void
    {
        $mockTeam = m::mock(Team::class);
        $mockTeam->shouldReceive('getId')->andReturn(123);

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('getRoles')->andReturn(new ArrayCollection([]));
        $mockUser->shouldReceive('getId')->andReturn(291);
        $mockUser->shouldReceive('getTeam')->andReturn($mockTeam)->getMock();

        $mockIdentity = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn($mockUser)->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')
            ->andReturn($mockIdentity);

        $contactDetails = m::mock(ContactDetails::class);
        $contactDetails->shouldReceive('getEmailAddress')->andReturn('someEmail@email.com');

        $mockUsers = array_map(
            static function ($emailAddress) {
                $mockUserCD = m::Mock(ContactDetails::class)
                    ->shouldReceive('getEmailAddress')
                    ->andReturn($emailAddress)
                    ->getMock();

                $mockUser = m::Mock(User::class)
                    ->shouldReceive('getContactDetails')
                    ->andReturn($mockUserCD)
                    ->getMock();

                return m::Mock(OrganisationUser::class)
                    ->shouldReceive('getUser')
                    ->andReturn($mockUser)
                    ->getMock();
            },
            ['firstEmail@test.com', 'secondEmail@test.com']
        );

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getAdminOrganisationUsers')->andReturn(new ArrayCollection($mockUsers));

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getId')->andReturn(7)->getMock();
        $licence->shouldReceive('getTranslateToWelsh')->andReturn(true);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($contactDetails);

        $document = m::mock(Document::class)->shouldReceive('getId')->andReturn(10)->getMock();
        $document->shouldReceive('getDescription')->andReturn('Test Document');

        $this->repoMap['Licence']->shouldReceive('fetchById')->andReturn($licence);
        $this->repoMap['Document']->shouldReceive('fetchById')->andReturn($document);

        $expectedTaskData = [
            'category' => Category::CATEGORY_COMPLIANCE,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
            'description' => 'Check response to PTR',
            'actionDate' => (new DateTime('now'))->add(new \DateInterval('P21D'))->format('Y-m-d'),
            'assignedToUser' => 291,
            'assignedToTeam' => 123,
            'licence' => 7,
            'urgent' => 'Y'
        ];

        $expectedDeleteDocumentData = [
            'id' => 10
        ];

        $expectedAddressData = [
            'template' => 10,
            'licence' => 7,
            'query' => [
                'licence' => 7
            ],
            'category' => Category::CATEGORY_COMPLIANCE,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
            'isExternal' => false,
            'description' => 'Test Document',
            'metadata' => json_encode([
                'details' => [
                    'category' => Category::CATEGORY_COMPLIANCE,
                    'documentSubCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
                    'documentTemplate' => 818,
                    'allowEmail' => true
                ]
            ]),
        ];

        $result = new Result();

        $this->expectedSideEffect(CreateQueueMessage::class, [], new Result(), 3);
        $this->expectedSideEffect(CreateTask::class, $expectedTaskData, $result);
        $this->expectedSideEffect(DeleteDocument::class, $expectedDeleteDocumentData, $result);

        $expectedPrintLettersData = [
            'id' => 100,
            'method' => 'printAndPost'
        ];

        $expectedPrintLettersEmailData = [
            'id' => 100,
            'method' => 'email'
        ];

        $result = new Result();
        $result->addId('document', 100, true);
        $this->expectedSideEffect(GenerateAndStore::class, $expectedAddressData, $result);
        $this->expectedSideEffect(PrintLetter::class, $expectedPrintLettersData, $result);
        $this->expectedSideEffect(PrintLetter::class, $expectedPrintLettersEmailData, $result);

        $cmd = ProposeToRevokeCmd::create(['licence' => 7, 'document' => 10]);
        $result = $this->sut->handleCommand($cmd);

        $expectedResult = [
            'id' => [
                'document' => 100
            ],
            'messages' => ['Propose to revoke successfully processed']
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }

    public function testHandleCommandNoEmails(): void
    {
        $mockTeam = m::mock(Team::class);
        $mockTeam->shouldReceive('getId')->andReturn(123);

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('getRoles')->andReturn(new ArrayCollection([]));
        $mockUser->shouldReceive('getId')->andReturn(291);
        $mockUser->shouldReceive('getTeam')->andReturn($mockTeam)->getMock();

        $mockIdentity = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn($mockUser)->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')
            ->andReturn($mockIdentity);

        $contactDetails = m::mock(ContactDetails::class);
        $contactDetails->shouldReceive('getEmailAddress')->andReturn(null);

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getAdminOrganisationUsers')->andReturn(new ArrayCollection([]));

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getId')->andReturn(7)->getMock();
        $licence->shouldReceive('getTranslateToWelsh')->andReturn(true);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($contactDetails);

        $document = m::mock(Document::class)->shouldReceive('getId')->andReturn(10)->getMock();
        $document->shouldReceive('getDescription')->andReturn('Test Document');

        $this->repoMap['Licence']->shouldReceive('fetchById')->andReturn($licence);
        $this->repoMap['Document']->shouldReceive('fetchById')->andReturn($document);

        $expectedTaskData = [
            'category' => Category::CATEGORY_COMPLIANCE,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
            'description' => 'Check response to PTR',
            'actionDate' => (new DateTime('now'))->add(new \DateInterval('P21D'))->format('Y-m-d'),
            'assignedToUser' => 291,
            'assignedToTeam' => 123,
            'licence' => 7,
            'urgent' => 'Y'
        ];

        $expectedDeleteDocumentData = [
            'id' => 10
        ];

        $expectedAddressData = [
            'template' => 10,
            'licence' => 7,
            'query' => [
                'licence' => 7
            ],
            'category' => Category::CATEGORY_COMPLIANCE,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
            'isExternal' => false,
            'description' => 'Test Document',
            'metadata' => json_encode([
                'details' => [
                    'category' => Category::CATEGORY_COMPLIANCE,
                    'documentSubCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
                    'documentTemplate' => 818,
                    'allowEmail' => true
                ]
            ]),
        ];

        $expectedPrintLettersData = [
            'id' => 100,
            'method' => 'printAndPost'
        ];

        $result = new Result();

        $this->expectedSideEffect(CreateTask::class, $expectedTaskData, $result);
        $this->expectedSideEffect(DeleteDocument::class, $expectedDeleteDocumentData, $result);

        $result = new Result();
        $result->addId('document', 100);
        $result->addId('correspondenceAddress', 100);

        $this->expectedSideEffect(GenerateAndStore::class, $expectedAddressData, $result);
        $this->expectedSideEffect(PrintLetter::class, $expectedPrintLettersData, $result);

        $cmd = ProposeToRevokeCmd::create(['licence' => 7, 'document' => 10]);
        $result = $this->sut->handleCommand($cmd);

        $expectedResult = [
            'id' => [
                'document' => 100,
                'correspondenceAddress' => 100
            ],
            'messages' => [
                'Unable to send emails: No email addresses found',
                'Propose to revoke successfully processed'
            ]
        ];
        $this->assertEquals($expectedResult, $result->toArray());
    }

    public function testHandleCommandUnregisteredUser(): void
    {
        $mockTeam = m::mock(Team::class);
        $mockTeam->shouldReceive('getId')->andReturn(123);

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('getRoles')->andReturn(new ArrayCollection([]));
        $mockUser->shouldReceive('getId')->andReturn(291);
        $mockUser->shouldReceive('getTeam')->andReturn($mockTeam)->getMock();

        $mockIdentity = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn($mockUser)->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')
            ->andReturn($mockIdentity);

        $contactDetails = m::mock(ContactDetails::class);
        $contactDetails->shouldReceive('getEmailAddress')->andReturn('someEmail@email.com');

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getAdminOrganisationUsers')->andReturn(new ArrayCollection([]));

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getId')->andReturn(7)->getMock();
        $licence->shouldReceive('getTranslateToWelsh')->andReturn(true);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($contactDetails);

        $document = m::mock(Document::class)->shouldReceive('getId')->andReturn(10)->getMock();
        $document->shouldReceive('getDescription')->andReturn('Test Document');

        $this->repoMap['Licence']->shouldReceive('fetchById')->andReturn($licence);
        $this->repoMap['Document']->shouldReceive('fetchById')->andReturn($document);

        $expectedTaskData = [
            'category' => Category::CATEGORY_COMPLIANCE,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
            'description' => 'Check response to PTR',
            'actionDate' => (new DateTime('now'))->add(new \DateInterval('P21D'))->format('Y-m-d'),
            'assignedToUser' => 291,
            'assignedToTeam' => 123,
            'licence' => 7,
            'urgent' => 'Y'
        ];

        $expectedDeleteDocumentData = [
            'id' => 10
        ];

        $expectedAddressData = [
            'template' => 10,
            'licence' => 7,
            'query' => [
                'licence' => 7
            ],
            'category' => Category::CATEGORY_COMPLIANCE,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
            'isExternal' => false,
            'description' => 'Test Document',
            'metadata' => json_encode([
                'details' => [
                    'category' => Category::CATEGORY_COMPLIANCE,
                    'documentSubCategory' => SubCategory::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION,
                    'documentTemplate' => 818,
                    'allowEmail' => true
                ]
            ]),
        ];

        $expectedPrintLettersData = [
            'id' => 100,
            'method' => 'printAndPost'
        ];

        $expectedPrintLettersEmailData = [
            'id' => 100,
            'method' => 'email'
        ];

        $result = new Result();

        $this->expectedSideEffect(CreateQueueMessage::class, [], new Result());
        $this->expectedSideEffect(CreateTask::class, $expectedTaskData, $result);
        $this->expectedSideEffect(DeleteDocument::class, $expectedDeleteDocumentData, $result);

        $result = new Result();
        $result->addId('document', 100, false);
        $result->addId('correspondenceAddress', 100);

        $this->expectedSideEffect(GenerateAndStore::class, $expectedAddressData, $result);
        $this->expectedSideEffect(PrintLetter::class, $expectedPrintLettersData, $result);
        $this->expectedSideEffect(PrintLetter::class, $expectedPrintLettersEmailData, $result);

        $cmd = ProposeToRevokeCmd::create(['licence' => 7, 'document' => 10]);
        $result = $this->sut->handleCommand($cmd);

        $expectedResult = [
            'id' => [
                'document' => 100,
                'correspondenceAddress' => 100
            ],
            'messages' => ['Propose to revoke successfully processed']
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }
}
