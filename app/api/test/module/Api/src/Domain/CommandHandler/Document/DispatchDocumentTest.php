<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\CreateDocumentSpecific;
use Dvsa\Olcs\Api\Domain\Command\Document\DispatchDocument as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Email\CreateCorrespondenceRecord;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTranslateToWelshTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\DispatchDocument as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Dispatch Document Test
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Document\DispatchDocument::class)]
class DispatchDocumentTest extends AbstractCommandHandlerTestCase
{
    public const DOC_ID = 9001;
    public const LIC_ID = 8001;

    /** @var \Dvsa\Olcs\Api\Domain\CommandHandler\Document\DispatchDocument */
    protected $sut;

    /** @var  m\MockInterface | ContactDetails  */
    private $mockContactDetails;
    /** @var  m\MockInterface | OrganisationUser */
    private $mockOrgUser;
    /** @var  m\MockInterface | Organisation */
    private $mockOrg;
    /** @var  Licence | m\MockInterface */
    private $mockLic;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', LicenceRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        $this->mockContactDetails = m::mock(ContactDetails::class)->makePartial();

        /** @var m\MockInterface | User  $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setContactDetails($this->mockContactDetails);

        /** @var OrganisationUser $orgUser */
        $this->mockOrgUser = m::mock(OrganisationUser::class)->makePartial();
        $this->mockOrgUser->setUser($mockUser);

        $this->mockOrg = m::mock(Organisation::class)->makePartial();
        $this->mockOrg->setAllowEmail('Y');

        $this->mockLic = m::mock(Licence::class)->makePartial();
        $this->mockLic
            ->setId(self::LIC_ID)
            ->setTranslateToWelsh('N')
            ->setOrganisation($this->mockOrg);

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')->with(self::LIC_ID)->andReturn($this->mockLic);

        parent::setUp();
    }

    public function testHandleCommandNoLicence(): void
    {
        $this->expectException(BadRequestException::class);

        $data = [];
        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNoDescription(): void
    {
        $this->expectException(BadRequestException::class);

        $data = [
            'licence' => self::LIC_ID
        ];
        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandEmailNoPrint(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'description' => 'foo',
            'user' => 1,
            'isEnforcePrint' => 'N',
            'printCopiesCount' => 777,
        ];
        $command = Cmd::create($data);

        $this->mockContactDetails->setEmailAddress('foo@bar.com');
        $this->mockOrg->shouldReceive('getAdminOrganisationUsers')->andReturn([$this->mockOrgUser]);

        $result1 = new Result();
        $result1->addId('document', self::DOC_ID);
        $dataForCreateDocSpecific = [
            'licence' => self::LIC_ID,
            'description' => 'foo',
            'user' => 1,
        ];
        $commandCreateDocSpecific = Cmd::create($dataForCreateDocSpecific);
        $this->expectedSideEffect(CreateDocumentSpecific::class, $commandCreateDocSpecific->getArrayCopy(), $result1);

        $data = [
            'licence' => self::LIC_ID,
            'document' => self::DOC_ID,
            'type' => 'standard'
        ];
        $result2 = new Result();
        $result2->addId('correspondenceInbox', 321);
        $this->expectedSideEffect(CreateCorrespondenceRecord::class, $data, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => self::DOC_ID,
                'correspondenceInbox' => 321
            ],
            'messages' => [
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandEnforcePrintNoEmail(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'identifier' => self::DOC_ID,
            'description' => 'foo',
            'isEnforcePrint' => 'Y',
            'printCopiesCount' => 777,
            'user' => 1,
        ];
        $command = Cmd::create($data);

        $this->mockOrg->setAllowEmail('N');

        $result1 = new Result();
        $result1->addId('document', self::DOC_ID);
        $dataForCreateDocSpecific = [
            'licence' => self::LIC_ID,
            'identifier' => self::DOC_ID,
            'description' => 'foo',
            'user' => 1
        ];
        $commandCreateDocSpecific = Cmd::create($dataForCreateDocSpecific);
        $this->expectedSideEffect(CreateDocumentSpecific::class, $commandCreateDocSpecific->getArrayCopy(), $result1);

        $data = [
            'documentId' => self::DOC_ID,
            'jobName' => 'foo',
            'user' => 1,
            'copies' => 777,
        ];
        $result2 = new Result();
        $result2->addMessage('Printed');
        $this->expectedSideEffect(Enqueue::class, $data, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => self::DOC_ID
            ],
            'messages' => [
                'Printed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandEmailAndEnforcePrint(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'description' => 'foo',
            'user' => 1,
            'isEnforcePrint' => 'Y',
            'printCopiesCount' => 777,
        ];
        $command = Cmd::create($data);

        $this->mockContactDetails->setEmailAddress('foo@bar.com');
        $this->mockOrg->shouldReceive('getAdminOrganisationUsers')->andReturn([$this->mockOrgUser]);

        $result1 = new Result();
        $result1->addId('document', self::DOC_ID);
        $dataForCreateDocSpecific = [
            'licence' => self::LIC_ID,
            'description' => 'foo',
            'user' => 1,
        ];
        $commandCreateDocSpecific = Cmd::create($dataForCreateDocSpecific);
        $this->expectedSideEffect(CreateDocumentSpecific::class, $commandCreateDocSpecific->getArrayCopy(), $result1);

        $data = [
            'documentId' => self::DOC_ID,
            'jobName' => 'foo',
            'user' => 1,
            'copies' => 777,
        ];
        $result2 = new Result();
        $result2->addMessage('Printed');
        $this->expectedSideEffect(Enqueue::class, $data, $result2);

        $data = [
            'licence' => self::LIC_ID,
            'document' => self::DOC_ID,
            'type' => 'standard'
        ];
        $result2 = new Result();
        $result2->addId('correspondenceInbox', 321);
        $this->expectedSideEffect(CreateCorrespondenceRecord::class, $data, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => self::DOC_ID,
                'correspondenceInbox' => 321
            ],
            'messages' => [
                'Printed'
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandPrint(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'identifier' => 'ABCself::DOC_ID',
            'description' => 'foo',
            'user' => 1,
        ];
        $command = Cmd::create($data);

        $this->mockOrg->setAllowEmail('N');

        $result1 = new Result();
        $result1->addId('document', self::DOC_ID);
        $dataForCreateDocSpecific = [
            'licence' => self::LIC_ID,
            'identifier' => 'ABCself::DOC_ID',
            'description' => 'foo',
            'user' => 1
        ];
        $commandCreateDocSpecific = Cmd::create($dataForCreateDocSpecific);
        $this->expectedSideEffect(CreateDocumentSpecific::class, $commandCreateDocSpecific->getArrayCopy(), $result1);

        $data = [
            'documentId' => self::DOC_ID,
            'jobName' => 'foo',
            'user' => 1,
            'copies' => null,
        ];
        $result2 = new Result();
        $result2->addMessage('Printed');
        $this->expectedSideEffect(Enqueue::class, $data, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => self::DOC_ID
            ],
            'messages' => [
                'Printed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandPrintWelsh(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'identifier' => 'ABCself::DOC_ID',
            'description' => 'foo',
            'user' => 1
        ];
        $command = Cmd::create($data);

        $this->mockOrg->shouldReceive('getAdminOrganisationUsers')->andReturn([$this->mockOrgUser]);
        $this->mockLic->setTranslateToWelsh('Y');

        $result1 = new Result();
        $result1->addId('document', self::DOC_ID);
        $dataForCreateDocSpecific = [
            'licence' => self::LIC_ID,
            'identifier' => 'ABCself::DOC_ID',
            'description' => 'foo',
            'user' => 1
        ];
        $commandCreateDocSpecific = Cmd::create($dataForCreateDocSpecific);
        $this->expectedSideEffect(CreateDocumentSpecific::class, $commandCreateDocSpecific->getArrayCopy(), $result1);

        $data = [
            'documentId' => self::DOC_ID,
            'jobName' => 'foo',
            'user' => 1
        ];
        $result2 = new Result();
        $result2->addMessage('Printed');
        $this->expectedSideEffect(Enqueue::class, $data, $result2);

        $data = [
            'description' => 'foo',
            'licence' => self::LIC_ID
        ];
        $result3 = new Result();
        $result3->addMessage('Task created');
        $this->expectedSideEffect(CreateTranslateToWelshTask::class, $data, $result3);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => self::DOC_ID
            ],
            'messages' => [
                'Task created',
                'Printed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
