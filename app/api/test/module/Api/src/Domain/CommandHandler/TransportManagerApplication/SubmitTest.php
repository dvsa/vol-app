<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Command\TransportManagerApplication\Snapshot;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication\Submit as CommandHandler;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Submit as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Email\Service\TemplateRenderer;

/**
 * SubmitTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class SubmitTest extends AbstractCommandHandlerTestCase
{
    protected $loggedInUser;

    protected $applicationId = 234;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo(
            'TransportManagerApplication',
            \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication::class
        );
        $this->mockRepo(
            'TransportManager',
            \Dvsa\Olcs\Api\Domain\Repository\TransportManager::class
        );

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            TransportManagerApplication::STATUS_TM_SIGNED,
            TransportManagerApplication::STATUS_OPERATOR_SIGNED,
            TransportManagerApplication::TYPE_EXTERNAL,
            TransportManagerApplication::STATUS_RECEIVED,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithVersion(): void
    {
        $command = Command::create(['id' => 863, 'version' => 234]);

        $organisation = new Organisation();

        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->setIsOwner('N');

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence->getId')->andReturn(111);
        $application->shouldReceive('getId')->andReturn(234);
        $application->shouldReceive('getLicence->getOrganisation')->with()->once()
            ->andReturn($organisation);
        $application->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION));

        $tma->shouldReceive('getApplication')->times(3)
            ->andReturn($application);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 234)->andReturn($tma);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()->andReturnUsing(
            function (TransportManagerApplication $tma) {
                $this->assertSame(
                    $this->refData[TransportManagerApplication::STATUS_TM_SIGNED],
                    $tma->getTmApplicationStatus()
                );
            }
        );

        $this->mockTask();

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithNextStatus(): void
    {
        $command = Command::create(['id' => 863, 'nextStatus' => TransportManagerApplication::STATUS_RECEIVED]);

        /** @var TransportManagerApplication $tma */
        $tma = m::mock(TransportManagerApplication::class)->makePartial();

        $tma->setIsOwner('N');
        $tma->setId(863);

        $tma->shouldReceive('getTransportManager->getTmType')
            ->with()
            ->once()
            ->andReturn($this->refData[TransportManagerApplication::TYPE_EXTERNAL]);
        $tma->shouldReceive('getTransportManager->getId')
            ->with()
            ->once()
            ->andReturn(111);

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($tma);

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (TransportManagerApplication $tma) {
                    $this->assertSame(
                        $this->refData[TransportManagerApplication::STATUS_RECEIVED],
                        $tma->getTmApplicationStatus()
                    );
                }
            );

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence->getId')->andReturn(111);
        $application->shouldReceive('getId')->andReturn(234);
        $application->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION));

        $this->mockTask();

        $tma->shouldReceive('getApplication')->twice()
            ->andReturn($application);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\TransportManagerApplication\Snapshot::class,
            ['id' => 863],
            new Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandIsOwnerY(): void
    {
        $command = Command::create(['id' => 863]);

        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->setIsOwner('Y');
        $tma->setId(1);
        $tma->setTmType(TransportManagerApplication::TYPE_EXTERNAL);
        $tma->setTransportManager(
            m::mock(TransportManager::class)->makePartial()->shouldReceive('getId')->once()->andReturn(1)->getMock()
        );

        $data = [
            'id' => 1,
            'user' => 1
        ];

        $result = new Result();
        $this->expectedSideEffect(Snapshot::class, $data, $result);

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence->getId')->andReturn(111);
        $application->shouldReceive('getId')->andReturn(234);
        $application->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION));

        $this->mockTask();

        $tma->shouldReceive('getApplication')->twice()
            ->andReturn($application);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command)->andReturn($tma);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()->andReturnUsing(
            function (TransportManagerApplication $tma) {
                $this->assertSame(
                    $this->refData[TransportManagerApplication::STATUS_OPERATOR_SIGNED],
                    $tma->getTmApplicationStatus()
                );
            }
        );
        $this->repoMap['TransportManager']->shouldReceive('save')->once()->andReturnUsing(
            function (TransportManager $tm) {
                $this->assertSame(
                    $this->refData[TransportManagerApplication::TYPE_EXTERNAL],
                    $tm->getTmType()
                );
            }
        );

        $this->sut->handleCommand($command);
    }

    public static function dataProviderTestHandleCommand(): array
    {
        return [
            [false, 'application'],
            [true, 'variation']
        ];
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestHandleCommand')]
    public function testHandleCommandSendEmailToAdminsRemoveDuplicate(mixed $isVariation, mixed $uriSegment): void
    {
        $command = Command::create(['id' => 863]);

        $creator = m::mock(User::class)->makePartial();
        $creator->setId(1);
        $creator->setTranslateToWelsh('N');
        $creator->shouldReceive('getContactDetails->getEmailAddress')->with()->once()
            ->andReturn('email1');

        $user = m::mock(User::class)->makePartial();
        $user->setId(2);
        $user->setTranslateToWelsh('Y');
        $user->shouldReceive('getContactDetails->getEmailAddress')->with()->once()
            ->andReturn('foo@bar.com');

        $orgUser = new OrganisationUser();
        $orgUser->setUser($user);
        $orgUser->setIsAdministrator('Y');

        $orgUserCreator = new OrganisationUser();
        $orgUserCreator->setUser($creator);
        $orgUserCreator->setIsAdministrator('Y');

        $organisation = new Organisation();
        $organisation->addOrganisationUsers($orgUser);
        $organisation->addOrganisationUsers($orgUserCreator);

        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->setId(12);
        $tma->shouldReceive('getCreatedBy')->with()->andReturn($creator);

        $tma->shouldReceive('getTransportManager->getHomeCd->getPerson->getFullName')->with()->once()
            ->andReturn('Bob Smith');

        $this->applicationId = 76;
        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence->getLicNo')->andReturn('LIC01');
        $application->shouldReceive('getId')->andReturn(76);
        $application->shouldReceive('getLicence->getId')->andReturn(111);
        $application->shouldReceive('getIsVariation')->with()->once()->andReturn($isVariation);
        $application->shouldReceive('getLicence->getOrganisation')->with()->once()
        ->andReturn($organisation);
        $application->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION));
        $this->mockTask();

        $tma->shouldReceive('getApplication')->times(7)
            ->andReturn($application);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command)->andReturn($tma);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once();

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->times(2)->andReturnUsing(
            function (\Dvsa\Olcs\Email\Data\Message $message, $template, $vars, $layout) use ($uriSegment) {

                $this->assertSame('email.transport-manager-submitted-form.subject', $message->getSubject());
                $this->assertSame('foo@bar.com', $message->getTo());
                $this->assertSame('cy_GB', $message->getLocale());
                $this->assertSame('transport-manager-submitted-form', $template);
                $this->assertSame(
                    [
                        'tmFullName' => 'Bob Smith',
                        'licNo' => 'LIC01',
                        'applicationId' => 76,
                        'tmaUrl' => 'http://selfserve/' . $uriSegment . '/76/transport-managers/details/12/'
                    ],
                    $vars
                );
                $this->assertSame('default', $layout);
            },
            function (\Dvsa\Olcs\Email\Data\Message $message, $template, $vars, $layout) use ($uriSegment) {

                $this->assertSame('email.transport-manager-submitted-form.subject', $message->getSubject());
                $this->assertSame('email1', $message->getTo());
                $this->assertSame('en_GB', $message->getLocale());
                $this->assertSame('transport-manager-submitted-form', $template);
                $this->assertSame(
                    [
                        'tmFullName' => 'Bob Smith',
                        'licNo' => 'LIC01',
                        'applicationId' => 76,
                        'tmaUrl' => 'http://selfserve/' . $uriSegment . '/76/transport-managers/details/12/'
                    ],
                    $vars
                );
                $this->assertSame('default', $layout);
            }
        );

        $result = new Result();
        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => 'foo@bar.com'
            ],
            $result
        );
        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => 'email1'
            ],
            $result
        );

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestHandleCommand')]
    public function testHandleCommandSendEmailToCreator(mixed $isVariation, mixed $uriSegment): void
    {
        $command = Command::create(['id' => 863]);

        $creator = m::mock(User::class)->makePartial();
        $creator->setId(1);
        $creator->setTranslateToWelsh('N');
        $creator->shouldReceive('getContactDetails->getEmailAddress')->with()->once()
            ->andReturn('email1');

        $user = m::mock(User::class)->makePartial();
        $user->setId(2);
        $user->setTranslateToWelsh('Y');
        $user->shouldReceive('getContactDetails->getEmailAddress')->with()->once()
            ->andReturn('foo@bar.com');

        $orgUser = new OrganisationUser();
        $orgUser->setUser($user);
        $orgUser->setIsAdministrator('Y');

        $organisation = new Organisation();
        $organisation->addOrganisationUsers($orgUser);

        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->setId(12);
        $tma->shouldReceive('getCreatedBy')->with()->andReturn($creator);

        $this->applicationId = 76;
        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence->getLicNo')->andReturn('LIC01');
        $application->shouldReceive('getId')->andReturn(76);
        $application->shouldReceive('getLicence->getId')->andReturn(111);
        $application->shouldReceive('getIsVariation')->with()->once()->andReturn($isVariation);
        $application->shouldReceive('getLicence->getOrganisation')->with()->once()
            ->andReturn($organisation);
        $application->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION));
        $this->mockTask();

        $tma->shouldReceive('getApplication')->times(7)
            ->andReturn($application);

        $tma->shouldReceive('getTransportManager->getHomeCd->getPerson->getFullName')->with()->once()
            ->andReturn('Bob Smith');

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command)->andReturn($tma);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once();

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->times(2)->andReturnUsing(
            function (\Dvsa\Olcs\Email\Data\Message $message, $template, $vars, $layout) use ($uriSegment) {

                $this->assertSame('email.transport-manager-submitted-form.subject', $message->getSubject());
                $this->assertSame('foo@bar.com', $message->getTo());
                $this->assertSame('cy_GB', $message->getLocale());
                $this->assertSame('transport-manager-submitted-form', $template);
                $this->assertSame(
                    [
                        'tmFullName' => 'Bob Smith',
                        'licNo' => 'LIC01',
                        'applicationId' => 76,
                        'tmaUrl' => 'http://selfserve/' . $uriSegment . '/76/transport-managers/details/12/'
                    ],
                    $vars
                );
                $this->assertSame('default', $layout);
            },
            function (\Dvsa\Olcs\Email\Data\Message $message, $template, $vars, $layout) use ($uriSegment) {

                $this->assertSame('email.transport-manager-submitted-form.subject', $message->getSubject());
                $this->assertSame('email1', $message->getTo());
                $this->assertSame('en_GB', $message->getLocale());
                $this->assertSame('transport-manager-submitted-form', $template);
                $this->assertSame(
                    [
                        'tmFullName' => 'Bob Smith',
                        'licNo' => 'LIC01',
                        'applicationId' => 76,
                        'tmaUrl' => 'http://selfserve/' . $uriSegment . '/76/transport-managers/details/12/'
                    ],
                    $vars
                );
                $this->assertSame('default', $layout);
            }
        );

        $result = new Result();
        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => 'foo@bar.com'
            ],
            $result
        );
        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => 'email1'
            ],
            $result
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandSkipTask(): void
    {
        $command = Command::create(['id' => 863, 'version' => 234]);

        $organisation = new Organisation();

        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->setIsOwner('N');

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence->getId')->andReturn(111);
        $application->shouldReceive('getId')->andReturn(234);
        $application->shouldReceive('getLicence->getOrganisation')->with()->once()
            ->andReturn($organisation);
        $application->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_NOT_SUBMITTED));

        $tma->shouldReceive('getApplication')->twice()
            ->andReturn($application);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 234)->andReturn($tma);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()->andReturnUsing(
            function (TransportManagerApplication $tma) {
                $this->assertSame(
                    $this->refData[TransportManagerApplication::STATUS_TM_SIGNED],
                    $tma->getTmApplicationStatus()
                );
            }
        );

        $this->sut->handleCommand($command);
    }

    private function mockTask(): void
    {
        $taskData = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_TM1_DIGITAL,
            'description' => 'Transport Manager form submitted',
            'isClosed' => 'N',
            'urgent' => 'N',
            'application' => $this->applicationId,
            'licence' => 111
        ];
        $result = new Result();
        $result->addMessage('TM Application Submitted');
        $this->expectedSideEffect(CreateTask::class, $taskData, $result);
    }
}
