<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Correspondence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Correspondence\ProcessInboxDocuments as Command;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Correspondence\ProcessInboxDocuments;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Correspondence\ProcessInboxDocuments::class)]
class ProcessInboxDocumentsTest extends AbstractCommandHandlerTestCase
{
    public const LIC_ID = 999;
    public const ORG_ID = 7777;

    /** @var  m\MockInterface */
    private $mockTempRenderer;

    /** @var ProcessInboxDocuments  */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ProcessInboxDocuments();
        $this->mockRepo('CorrespondenceInbox', Repository\CorrespondenceInbox::class);

        $this->mockTempRenderer = m::mock(TemplateRenderer::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => $this->mockTempRenderer,
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create([]);
        $orgName = 'org name';

        $cd = new ContactDetails(m::mock(RefData::class));
        $cd->setEmailAddress('foo@bar.com');

        $user = new User('pid', 'TYPE');
        $user->setContactDetails($cd);
        $user->setTranslateToWelsh('Y');

        $orgUser = new OrganisationUser();
        $orgUser->setUser($user);
        $orgUser->setIsAdministrator('Y');

        $organisation = new Organisation();
        $organisation->addOrganisationUsers($orgUser);
        $organisation->setName($orgName);

        $mockLicence = m::mock()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getOrganisation')
            ->andReturn($organisation)
            ->shouldReceive('getLicNo')
            ->andReturn('licNo')
            ->once()
            ->getMock();

        $mockDocument = m::mock()
            ->shouldReceive('getContinuationDetails')
            ->andReturn(
                [
                    m::mock()
                    ->shouldReceive('getChecklistDocument')
                    ->andReturn('foo')
                    ->once()
                    ->getMock()
                ]
            )
            ->once()
            ->getMock();

        $mockInboxRecord = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->once()
            ->shouldReceive('setEmailReminderSent')
            ->with('Y')
            ->once()
            ->shouldReceive('getDocument')
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $this->repoMap['CorrespondenceInbox']
            ->shouldReceive('getAllRequiringReminder')
            ->with(m::type(\DateTime::class), m::type(\DateTime::class))
            ->andReturn([$mockInboxRecord])
            ->once()
            ->shouldReceive('save')
            ->with($mockInboxRecord)
            ->once()
            ->getMock();

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')
            ->once()
            ->with(
                m::type(Message::class),
                'email-inbox-reminder-continuation',
                ['licNo' => 'licNo', 'operatorName' => $orgName, 'url' => 'http://selfserve/correspondence'],
                'default'
            );

        $result = new Result();
        $data = [
            'to' => 'foo@bar.com'
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $expected = [
            'messages' => [
                'Found 1 records to email',
                'Sending email reminder for licence 1 to foo@bar.com',
            ],
            'id' => []
        ];
        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandFailSendEmail(): void
    {
        $command = Command::create([]);

        $cd = new ContactDetails(m::mock(RefData::class));
        $cd->setEmailAddress('foo@bar.com');

        $user = new User('pid', 'TYPE');
        $user->setContactDetails($cd);
        $user->setTranslateToWelsh('Y');

        $orgUser = (new OrganisationUser())
            ->setUser($user)
            ->setIsAdministrator('Y');

        $organisation = (new Organisation())
            ->setId(self::ORG_ID)
            ->addOrganisationUsers(new ArrayCollection([$orgUser]));

        $mockLicence = m::mock()
            ->shouldReceive('getId')->once()->andReturn(self::LIC_ID)
            ->shouldReceive('getOrganisation')->andReturn($organisation)
            ->shouldReceive('getLicNo')->once()->andReturn('licNo')
            ->getMock();

        $mockDocument = m::mock()
            ->shouldReceive('getContinuationDetails')
            ->once()
            ->andReturn(
                [
                    m::mock()
                        ->shouldReceive('getChecklistDocument')->andReturn('foo')->once()
                        ->getMock(),
                ]
            )
            ->getMock();

        $mockInboxRecord = m::mock()
            ->shouldReceive('getDocument')->once()->andReturn($mockDocument)
            ->shouldReceive('getLicence')->once()->andReturn($mockLicence)
            ->shouldReceive('setEmailReminderSent')->never()
            ->getMock();

        $this->repoMap['CorrespondenceInbox']
            ->shouldReceive('getAllRequiringReminder')->once()->andReturn([$mockInboxRecord])
            ->shouldReceive('save')->never()
            ->getMock();

        $this->mockTempRenderer
            ->shouldReceive('renderBody')
            ->once()
            ->andThrow(new \Dvsa\Olcs\Email\Exception\EmailNotSentException('Unit_ErrMsg'));

        $actual = $this->sut->handleCommand($command);

        static::assertInstanceOf(Result::class, $actual);
        static::assertEquals(
            [
                'Found 1 records to email',
                sprintf(ProcessInboxDocuments::ERR_SEND_REMINDER, self::LIC_ID, self::ORG_ID),
            ],
            $actual->getMessages()
        );
    }

    public function testHandleCommandNoUsers(): void
    {
        $command = Command::create([]);

        $organisation = new Organisation();

        $mockLicence = m::mock()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getOrganisation')
            ->andReturn($organisation)
            ->once()
            ->getMock();

        $mockDocument = m::mock()
            ->shouldReceive('getContinuationDetails')
            ->andReturn(
                [
                    m::mock()
                        ->shouldReceive('getChecklistDocument')
                        ->andReturn('foo')
                        ->once()
                        ->getMock()
                ]
            )
            ->once()
            ->getMock();

        $mockInboxRecord = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->once()
            ->shouldReceive('getDocument')
            ->andReturn($mockDocument)
            ->once()
            ->getMock();

        $this->repoMap['CorrespondenceInbox']
            ->shouldReceive('getAllRequiringReminder')
            ->with(m::type(\DateTime::class), m::type(\DateTime::class))
            ->andReturn([$mockInboxRecord])
            ->once()
            ->getMock();

        $expected = [
            'messages' => [
                'Found 1 records to email',
                'No admin email addresses for licence 1',
            ],
            'id' => []
        ];
        $result = $this->sut->handleCommand($command);
        $this->assertEquals($result->toArray(), $expected);
    }
}
