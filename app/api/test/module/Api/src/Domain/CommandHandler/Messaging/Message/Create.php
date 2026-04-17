<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Message;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Message\Create as CreateMessageHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository\Conversation as ConversationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Message as MessageRepo;
use Dvsa\Olcs\Api\Domain\Repository\MessageContent as MessageContentRepo;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingContent;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class Create extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];

        $this->sut = new CreateMessageHandler();
        $this->mockRepo(ConversationRepo::class, ConversationRepo::class);
        $this->mockRepo(MessageRepo::class, MessageRepo::class);
        $this->mockRepo(MessageContentRepo::class, MessageContentRepo::class);
        $this->mockRepo(TaskRepo::class, TaskRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'This is a test!',
        ];

        $command = CreateMessageCommand::create($data);

        $mockTask = m::mock(Task::class);
        $mockTask->expects('setDescription')
            ->once();
        $mockTask->expects('setActionDate')
            ->once();
        $mockTask->expects('getId')
            ->once()
            ->andReturn(1);
        $mockTask->expects('getDescription')
            ->once()
            ->andReturn('Desc');
        $mockTask->expects('getActionDate')
            ->once()
            ->andReturn(new \DateTimeImmutable());

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getId')->andReturn(1);
        $mockConversation->expects('getIsClosed')->andReturn(0);
        $mockConversation->expects('getIsArchived')->andReturn(0);

        $mockConversation->expects('getTask')
            ->once()
            ->andReturn($mockTask);

        $this->repoMap[ConversationRepo::class]
            ->expects('fetchById')
            ->twice()
            ->with($conversationId)
            ->andReturn($mockConversation);
        $this->repoMap[MessageContentRepo::class]->expects('save')->with(m::type(MessagingContent::class));
        $this->repoMap[MessageRepo::class]->expects('save')->with(m::type(MessagingMessage::class));

        $this->mockedSmServices[AuthorizationService::class]
            ->expects('isGranted')
            ->times(3)
            ->andReturn(true);

        $this->repoMap[TaskRepo::class]
            ->expects('save')
            ->once()
            ->with(m::type(Task::class));

        $result = $this->sut->handleCommand($command);

        $this->assertArrayHasKey('id', $result->toArray());
        $this->assertArrayHasKey('message', $result->toArray()['id']);
        $this->assertArrayHasKey('messageContent', $result->toArray()['id']);
        $this->assertArrayHasKey('messageConversation', $result->toArray()['id']);
        $this->assertArrayHasKey('messages', $result->toArray());
    }

    public function testExternalUserMessageDoesNotUpdateActionDateWhenLastMessageWasExternal(): void
    {
        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'Another external message',
        ];

        $command = CreateMessageCommand::create($data);

        $existingActionDate = new \DateTime('2026-02-15');

        $mockExternalUser = m::mock(User::class);
        $mockExternalUser->expects('isInternal')->andReturn(false);

        $mockLastMessage = m::mock(MessagingMessage::class);
        $mockLastMessage->expects('getCreatedBy')->andReturn($mockExternalUser);

        $mockTask = m::mock(Task::class);
        $mockTask->expects('setDescription')->once();
        // Should NOT call setActionDate with a new date
        $mockTask->expects('getActionDate')->once()->with(true)->andReturn($existingActionDate);
        $mockTask->expects('setActionDate')->once()->with($existingActionDate); // Re-set existing date
        $mockTask->expects('getId')->once()->andReturn(1);
        $mockTask->expects('getDescription')->once()->andReturn('New message');
        $mockTask->expects('getActionDate')->once()->andReturn($existingActionDate);

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getId')->andReturn(1);
        $mockConversation->expects('getIsClosed')->andReturn(0);
        $mockConversation->expects('getIsArchived')->andReturn(0);
        $mockConversation->expects('getTask')->andReturn($mockTask);

        $this->repoMap[ConversationRepo::class]
            ->expects('fetchById')
            ->twice()
            ->with($conversationId)
            ->andReturn($mockConversation);

        $this->repoMap[MessageRepo::class]
            ->expects('fetchLastMessageByConversation')
            ->once()
            ->with($conversationId)
            ->andReturn($mockLastMessage);

        $this->repoMap[MessageContentRepo::class]->expects('save')->with(m::type(MessagingContent::class));
        $this->repoMap[MessageRepo::class]->expects('save')->with(m::type(MessagingMessage::class));

        // Mock as external user
        $this->mockedSmServices[AuthorizationService::class]
            ->expects('isGranted')
            ->times(3)
            ->andReturnUsing(function ($permission) {
                return $permission !== 'internal-user'; // Return false for internal-user check
            });

        $this->repoMap[TaskRepo::class]
            ->expects('save')
            ->once()
            ->with(m::type(Task::class));

        $result = $this->sut->handleCommand($command);

        $this->assertArrayHasKey('id', $result->toArray());
    }

    public function testExternalUserMessageUpdatesActionDateWhenLastMessageWasInternal(): void
    {
        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'Response to internal user',
        ];

        $command = CreateMessageCommand::create($data);

        $mockInternalUser = m::mock(User::class);
        $mockInternalUser->expects('isInternal')->andReturn(true);

        $mockLastMessage = m::mock(MessagingMessage::class);
        $mockLastMessage->expects('getCreatedBy')->andReturn($mockInternalUser);

        $mockTask = m::mock(Task::class);
        $mockTask->expects('setDescription')->once();
        $mockTask->expects('setActionDate')->once()->with(m::type(\DateTime::class)); // Should set NEW date
        $mockTask->expects('getId')->once()->andReturn(1);
        $mockTask->expects('getDescription')->once()->andReturn('New message');
        $mockTask->expects('getActionDate')->once()->andReturn(new \DateTime());

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getId')->andReturn(1);
        $mockConversation->expects('getIsClosed')->andReturn(0);
        $mockConversation->expects('getIsArchived')->andReturn(0);
        $mockConversation->expects('getTask')->andReturn($mockTask);

        $this->repoMap[ConversationRepo::class]
            ->expects('fetchById')
            ->twice()
            ->with($conversationId)
            ->andReturn($mockConversation);

        $this->repoMap[MessageRepo::class]
            ->expects('fetchLastMessageByConversation')
            ->once()
            ->with($conversationId)
            ->andReturn($mockLastMessage);

        $this->repoMap[MessageContentRepo::class]->expects('save')->with(m::type(MessagingContent::class));
        $this->repoMap[MessageRepo::class]->expects('save')->with(m::type(MessagingMessage::class));

        // Mock as external user
        $this->mockedSmServices[AuthorizationService::class]
            ->expects('isGranted')
            ->times(3)
            ->andReturnUsing(function ($permission) {
                return $permission !== 'internal-user';
            });

        $this->repoMap[TaskRepo::class]
            ->expects('save')
            ->once()
            ->with(m::type(Task::class));

        $result = $this->sut->handleCommand($command);

        $this->assertArrayHasKey('id', $result->toArray());
    }

    public function testInternalUserMessageAlwaysUpdatesActionDate(): void
    {
        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'Internal response',
        ];

        $command = CreateMessageCommand::create($data);

        $mockTask = m::mock(Task::class);
        $mockTask->expects('setDescription')->once();
        $mockTask->expects('setActionDate')->once()->with(m::type(\DateTime::class)); // Should always update
        $mockTask->expects('getId')->once()->andReturn(1);
        $mockTask->expects('getDescription')->once()->andReturn('Awaiting external response');
        $mockTask->expects('getActionDate')->once()->andReturn(new \DateTime());

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getId')->andReturn(1);
        $mockConversation->expects('getIsClosed')->andReturn(0);
        $mockConversation->expects('getIsArchived')->andReturn(0);
        $mockConversation->expects('getTask')->andReturn($mockTask);

        $this->repoMap[ConversationRepo::class]
            ->expects('fetchById')
            ->twice()
            ->with($conversationId)
            ->andReturn($mockConversation);

        // Should NOT call fetchLastMessageByConversation for internal users
        $this->repoMap[MessageRepo::class]
            ->expects('fetchLastMessageByConversation')
            ->never();

        $this->repoMap[MessageContentRepo::class]->expects('save')->with(m::type(MessagingContent::class));
        $this->repoMap[MessageRepo::class]->expects('save')->with(m::type(MessagingMessage::class));

        // Mock as internal user
        $this->mockedSmServices[AuthorizationService::class]
            ->expects('isGranted')
            ->times(3)
            ->andReturn(true); // Internal user

        $this->repoMap[TaskRepo::class]
            ->expects('save')
            ->once()
            ->with(m::type(Task::class));

        $result = $this->sut->handleCommand($command);

        $this->assertArrayHasKey('id', $result->toArray());
    }

    public function testCannotAddMessageToClosedConversation(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unable to create message on conversations that are closed or archived');

        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'This is a test!',
        ];

        $command = CreateMessageCommand::create($data);

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getIsClosed')->andReturn(1);
        $this->repoMap[ConversationRepo::class]->expects('fetchById')->with($conversationId)->andReturn(
            $mockConversation,
        );

        $this->sut->handleCommand($command);
    }

    public function testCannotAddMessageToArchivedConversation(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unable to create message on conversations that are closed or archived');

        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'This is a test!',
        ];

        $command = CreateMessageCommand::create($data);

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getIsClosed')->andReturn(0);
        $mockConversation->expects('getIsArchived')->andReturn(1);
        $this->repoMap[ConversationRepo::class]->expects('fetchById')->with($conversationId)->andReturn(
            $mockConversation,
        );

        $this->sut->handleCommand($command);
    }
}
