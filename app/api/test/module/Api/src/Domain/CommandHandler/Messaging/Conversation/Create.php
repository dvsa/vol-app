<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Conversation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Conversation\Create as CreateConversationHandler;
use Dvsa\Olcs\Api\Domain\Exception\Exception;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Create as CreateConversationCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class Create extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateConversationHandler();
        $this->mockRepo(Repository\Conversation::class, Repository\Conversation::class);
        $this->mockRepo(Repository\Task::class, Repository\Task::class);
        $this->mockRepo(Repository\MessagingSubject::class, Repository\MessagingSubject::class);
        $this->mockRepo(Repository\Application::class, Repository\Application::class);

        parent::setUp();
    }

    public function testHandleCommandThrowsExceptionWhenNeitherLicenceOrApplicationDefined(): void
    {
        $command = CreateConversationCommand::create($commandParameters = []);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Command expects either a application or licence defined');

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithApplicationResolvesLicence(): void
    {
        $command = CreateConversationCommand::create($commandParameters = [
            'application' => '1',
            'messageContent' => 'Test Message'
        ]);

        $mockLicence = m::mock(Entity\Licence\Licence::class);
        $mockLicence
            ->expects('getId')
            ->once()
            ->andReturn(2);

        $mockApplication = m::mock(Entity\Application\Application::class);
        $mockApplication
            ->expects('getLicence')
            ->once()
            ->andReturn($mockLicence);

        $this->repoMap[Repository\Application::class]
            ->expects('fetchById')
            ->once()
            ->with($commandParameters['application'])
            ->andReturn($mockApplication);

        $mockCategory = m::mock(Entity\System\Category::class);
        $mockCategory
            ->expects('getId')
            ->once()
            ->andReturn(3);

        $mockMessagingSubject = m::mock(Entity\Messaging\MessagingSubject::class);
        $mockMessagingSubject
            ->expects('getCategory')
            ->once()
            ->andReturn($mockCategory);
        $mockMessagingSubject
            ->expects('getSubCategory')
            ->once()
            ->andReturn(null);
        $mockMessagingSubject
            ->expects('getDescription')
            ->once()
            ->andReturn('Example description');

        $this->repoMap[Repository\MessagingSubject::class]
            ->expects('fetchById')
            ->once()
            ->andReturn($mockMessagingSubject);

        $this->expectedSideEffect(
            CreateTask::class,
            [
                'category' => 3,
                'licence' => 2,
                'application' => 1,
            ],
            (new Result())->addId('task', 4)
        );

        $mockTask = m::mock(Entity\Task\Task::class);
        $this->repoMap[Repository\Task::class]
            ->expects('fetchById')
            ->once()
            ->andReturn($mockTask);

        $this->repoMap[Repository\Conversation::class]
            ->expects('save')
            ->once()
            ->with(m::type(Entity\Messaging\MessagingConversation::class));

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Messaging\Message\Create::class,
            [
                'conversation' => null,
                'messageContent' => $commandParameters['messageContent'],
            ],
            (new Result())->addId('message', 5)
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $command = CreateConversationCommand::create($commandParameters = [
            'licence' => '1',
            'messageContent' => 'Test Message'
        ]);

        $mockApplication = m::mock(Entity\Application\Application::class);
        $mockApplication
            ->expects('getLicence')
            ->never();

        $this->repoMap[Repository\Application::class]
            ->expects('fetchById')
            ->never();

        $mockCategory = m::mock(Entity\System\Category::class);
        $mockCategory
            ->expects('getId')
            ->once()
            ->andReturn(3);

        $mockMessagingSubject = m::mock(Entity\Messaging\MessagingSubject::class);
        $mockMessagingSubject
            ->expects('getCategory')
            ->once()
            ->andReturn($mockCategory);
        $mockMessagingSubject
            ->expects('getSubCategory')
            ->once()
            ->andReturn(null);
        $mockMessagingSubject
            ->expects('getDescription')
            ->once()
            ->andReturn('Example description');

        $this->repoMap[Repository\MessagingSubject::class]
            ->expects('fetchById')
            ->once()
            ->andReturn($mockMessagingSubject);

        $this->expectedSideEffect(
            CreateTask::class,
            [
                'category' => 3,
                'licence' => 1,
            ],
            (new Result())->addId('task', 4)
        );

        $mockTask = m::mock(Entity\Task\Task::class);
        $this->repoMap[Repository\Task::class]
            ->expects('fetchById')
            ->once()
            ->andReturn($mockTask);

        $this->repoMap[Repository\Conversation::class]
            ->expects('save')
            ->once()
            ->with(m::type(Entity\Messaging\MessagingConversation::class));

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Messaging\Message\Create::class,
            [
                'conversation' => null,
                'messageContent' => $commandParameters['messageContent'],
            ],
            (new Result())->addId('message', 5)
        );

        $this->sut->handleCommand($command);
    }
}
