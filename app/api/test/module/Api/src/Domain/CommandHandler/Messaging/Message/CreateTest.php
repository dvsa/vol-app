<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Message;

use Dvsa\Olcs\Api\Domain\Command\Email\SendNewMessageNotificationToOperators;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Message\Create as CreateMessageHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository\Conversation as ConversationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Message as MessageRepo;
use Dvsa\Olcs\Api\Domain\Repository\MessageContent as MessageContentRepo;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingContent;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class CreateTest extends AbstractCommandHandlerTestCase
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

    /**
     * The handler interrogates the current user's role several times per run (task description, action date and the
     * email side effect all ask independently), so pin the answer per permission rather than the number of asks —
     * an exact call count here breaks on any refactor that reorders those private helpers.
     */
    private function mockCurrentUserIsInternal(bool $isInternal): void
    {
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn($isInternal);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(!$isInternal);
    }

    public function testHandleCommand(): void
    {
        $this->mockCurrentUserIsInternal(true);

        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'This is a test!',
        ];

        $command = CreateMessageCommand::create($data);

        $licenceId = 7;
        $mockLicence = m::mock(Licence::class);
        $mockLicence->expects('getId')->withNoArgs()->andReturn($licenceId);

        $mockTask = m::mock(Task::class);
        $mockTask->expects('setDescription')
            ->with(CreateMessageHandler::TASK_DESCRIPTION_ON_INTERNAL_REPLY);
        $mockTask->expects('setActionDate')
            ->with(m::type(\DateTime::class));
        $mockTask->expects('getId')
            ->withNoArgs()
            ->andReturn(1);
        $mockTask->expects('getDescription')
            ->withNoArgs()
            ->andReturn(CreateMessageHandler::TASK_DESCRIPTION_ON_INTERNAL_REPLY);
        $mockTask->expects('getActionDate')
            ->withNoArgs()
            ->andReturn(new \DateTimeImmutable());
        $mockTask->expects('getLicence')
            ->withNoArgs()
            ->andReturn($mockLicence);

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getId')->withNoArgs()->andReturn(1);
        $mockConversation->expects('getIsClosed')->withNoArgs()->andReturn(0);
        $mockConversation->expects('getIsArchived')->withNoArgs()->andReturn(0);

        // Once to attach the task description and action date, once to address the notification email.
        $mockConversation->shouldReceive('getTask')
            ->twice()
            ->withNoArgs()
            ->andReturn($mockTask);

        $this->repoMap[ConversationRepo::class]
            ->shouldReceive('fetchById')
            ->times(3)
            ->with($conversationId)
            ->andReturn($mockConversation);
        $this->repoMap[MessageContentRepo::class]->expects('save')->with(m::type(MessagingContent::class));
        $this->repoMap[MessageRepo::class]->expects('save')->with(m::type(MessagingMessage::class));

        $this->repoMap[TaskRepo::class]
            ->expects('save')
            ->with(m::type(Task::class));

        $this->expectedSideEffect(
            SendNewMessageNotificationToOperators::class,
            ['id' => $licenceId],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertArrayHasKey('id', $result->toArray());
        $this->assertArrayHasKey('message', $result->toArray()['id']);
        $this->assertArrayHasKey('messageContent', $result->toArray()['id']);
        $this->assertArrayHasKey('messageConversation', $result->toArray()['id']);
        $this->assertArrayHasKey('messages', $result->toArray());
    }

    public function testExternalUserMessageDoesNotUpdateActionDateWhenLastMessageWasExternal(): void
    {
        $this->mockCurrentUserIsInternal(false);

        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'Another external message',
        ];

        $command = CreateMessageCommand::create($data);

        $existingActionDate = new \DateTime('2026-02-15');

        $mockExternalUser = m::mock(User::class);
        $mockExternalUser->expects('isInternal')->withNoArgs()->andReturn(false);

        $mockLastMessage = m::mock(MessagingMessage::class);
        $mockLastMessage->expects('getCreatedBy')->withNoArgs()->andReturn($mockExternalUser);

        $mockTask = m::mock(Task::class);
        $mockTask->expects('setDescription')
            ->with(CreateMessageHandler::TASK_DESCRIPTION_ON_EXTERNAL_REPLY);
        // The existing date is read back and re-set rather than pushed out.
        $mockTask->expects('getActionDate')->with(true)->andReturn($existingActionDate);
        $mockTask->expects('setActionDate')->with($existingActionDate);
        $mockTask->expects('getId')->withNoArgs()->andReturn(1);
        $mockTask->expects('getDescription')
            ->withNoArgs()
            ->andReturn(CreateMessageHandler::TASK_DESCRIPTION_ON_EXTERNAL_REPLY);
        $mockTask->expects('getActionDate')->withNoArgs()->andReturn($existingActionDate);

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->shouldReceive('getId')->twice()->withNoArgs()->andReturn(1);
        $mockConversation->expects('getIsClosed')->withNoArgs()->andReturn(0);
        $mockConversation->expects('getIsArchived')->withNoArgs()->andReturn(0);
        $mockConversation->expects('getTask')->withNoArgs()->andReturn($mockTask);

        $this->repoMap[ConversationRepo::class]
            ->shouldReceive('fetchById')
            ->times(3)
            ->with($conversationId)
            ->andReturn($mockConversation);

        $this->repoMap[MessageRepo::class]
            ->expects('fetchLastMessageByConversation')
            ->with($conversationId)
            ->andReturn($mockLastMessage);

        $this->repoMap[MessageContentRepo::class]->expects('save')->with(m::type(MessagingContent::class));
        $this->repoMap[MessageRepo::class]->expects('save')->with(m::type(MessagingMessage::class));

        $this->repoMap[TaskRepo::class]
            ->expects('save')
            ->with(m::type(Task::class));

        $result = $this->sut->handleCommand($command);

        $this->assertArrayHasKey('id', $result->toArray());
    }

    public function testExternalUserMessageUpdatesActionDateWhenLastMessageWasInternal(): void
    {
        $this->mockCurrentUserIsInternal(false);

        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'Response to internal user',
        ];

        $command = CreateMessageCommand::create($data);

        $mockInternalUser = m::mock(User::class);
        $mockInternalUser->expects('isInternal')->withNoArgs()->andReturn(true);

        $mockLastMessage = m::mock(MessagingMessage::class);
        $mockLastMessage->expects('getCreatedBy')->withNoArgs()->andReturn($mockInternalUser);

        $mockTask = m::mock(Task::class);
        $mockTask->expects('setDescription')
            ->with(CreateMessageHandler::TASK_DESCRIPTION_ON_EXTERNAL_REPLY);
        $mockTask->expects('setActionDate')->with(m::type(\DateTime::class));
        $mockTask->expects('getId')->withNoArgs()->andReturn(1);
        $mockTask->expects('getDescription')
            ->withNoArgs()
            ->andReturn(CreateMessageHandler::TASK_DESCRIPTION_ON_EXTERNAL_REPLY);
        $mockTask->expects('getActionDate')->withNoArgs()->andReturn(new \DateTime());

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->shouldReceive('getId')->twice()->withNoArgs()->andReturn(1);
        $mockConversation->expects('getIsClosed')->withNoArgs()->andReturn(0);
        $mockConversation->expects('getIsArchived')->withNoArgs()->andReturn(0);
        $mockConversation->expects('getTask')->withNoArgs()->andReturn($mockTask);

        $this->repoMap[ConversationRepo::class]
            ->shouldReceive('fetchById')
            ->times(3)
            ->with($conversationId)
            ->andReturn($mockConversation);

        $this->repoMap[MessageRepo::class]
            ->expects('fetchLastMessageByConversation')
            ->with($conversationId)
            ->andReturn($mockLastMessage);

        $this->repoMap[MessageContentRepo::class]->expects('save')->with(m::type(MessagingContent::class));
        $this->repoMap[MessageRepo::class]->expects('save')->with(m::type(MessagingMessage::class));

        $this->repoMap[TaskRepo::class]
            ->expects('save')
            ->with(m::type(Task::class));

        $result = $this->sut->handleCommand($command);

        $this->assertArrayHasKey('id', $result->toArray());
    }

    public function testInternalUserMessageAlwaysUpdatesActionDate(): void
    {
        $this->mockCurrentUserIsInternal(true);

        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'Internal response',
        ];

        $command = CreateMessageCommand::create($data);

        $licenceId = 7;
        $mockLicence = m::mock(Licence::class);
        $mockLicence->expects('getId')->withNoArgs()->andReturn($licenceId);

        $mockTask = m::mock(Task::class);
        $mockTask->expects('setDescription')
            ->with(CreateMessageHandler::TASK_DESCRIPTION_ON_INTERNAL_REPLY);
        $mockTask->expects('setActionDate')->with(m::type(\DateTime::class));
        $mockTask->expects('getId')->withNoArgs()->andReturn(1);
        $mockTask->expects('getDescription')
            ->withNoArgs()
            ->andReturn(CreateMessageHandler::TASK_DESCRIPTION_ON_INTERNAL_REPLY);
        $mockTask->expects('getActionDate')->withNoArgs()->andReturn(new \DateTime());
        $mockTask->expects('getLicence')->withNoArgs()->andReturn($mockLicence);

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getId')->withNoArgs()->andReturn(1);
        $mockConversation->expects('getIsClosed')->withNoArgs()->andReturn(0);
        $mockConversation->expects('getIsArchived')->withNoArgs()->andReturn(0);
        $mockConversation->shouldReceive('getTask')->twice()->withNoArgs()->andReturn($mockTask);

        $this->repoMap[ConversationRepo::class]
            ->shouldReceive('fetchById')
            ->times(3)
            ->with($conversationId)
            ->andReturn($mockConversation);

        // Internal users never need to know who sent the previous message.
        $this->repoMap[MessageRepo::class]
            ->shouldReceive('fetchLastMessageByConversation')
            ->never();

        $this->repoMap[MessageContentRepo::class]->expects('save')->with(m::type(MessagingContent::class));
        $this->repoMap[MessageRepo::class]->expects('save')->with(m::type(MessagingMessage::class));

        $this->repoMap[TaskRepo::class]
            ->expects('save')
            ->with(m::type(Task::class));

        $this->expectedSideEffect(
            SendNewMessageNotificationToOperators::class,
            ['id' => $licenceId],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertArrayHasKey('id', $result->toArray());
    }

    public function testCannotAddMessageToClosedConversation(): void
    {
        $this->mockCurrentUserIsInternal(true);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unable to create message on conversations that are closed or archived');

        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'This is a test!',
        ];

        $command = CreateMessageCommand::create($data);

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getIsClosed')->withNoArgs()->andReturn(1);
        $this->repoMap[ConversationRepo::class]->expects('fetchById')->with($conversationId)->andReturn(
            $mockConversation,
        );

        $this->sut->handleCommand($command);
    }

    public function testCannotAddMessageToArchivedConversation(): void
    {
        $this->mockCurrentUserIsInternal(true);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unable to create message on conversations that are closed or archived');

        $data = [
            'conversation'   => $conversationId = 1,
            'messageContent' => 'This is a test!',
        ];

        $command = CreateMessageCommand::create($data);

        $mockConversation = m::mock(MessagingConversation::class);
        $mockConversation->expects('getIsClosed')->withNoArgs()->andReturn(0);
        $mockConversation->expects('getIsArchived')->withNoArgs()->andReturn(1);
        $this->repoMap[ConversationRepo::class]->expects('fetchById')->with($conversationId)->andReturn(
            $mockConversation,
        );

        $this->sut->handleCommand($command);
    }
}
