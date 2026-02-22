<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Conversation;

use Dvsa\Olcs\Api\Domain\Command\Email\CreateCorrespondenceRecord;
use Dvsa\Olcs\Api\Domain\Command\Messaging\Conversation\StoreEnhancedSnapshot;
use Dvsa\Olcs\Api\Domain\Command\Messaging\Conversation\StoreSnapshot;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Conversation\Close as CloseConversationHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Close as CloseConversationCommand;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class Close extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CloseConversationHandler();
        $this->mockRepo(Repository\Conversation::class, Repository\Conversation::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];

        $defaultMockTask = m::mock(Entity\Task\Task::class)
                            ->makePartial()
                            ->allows('getId')
                            ->getMock();
        $defaultMockConversation = m::mock(Entity\Messaging\MessagingConversation::class)
                                    ->makePartial()
                                    ->allows('getTask')
                                    ->andReturn($defaultMockTask)
                                    ->getMock()
                                    ->allows('getRelatedLicence')
                                    ->getMock();
        $this->repoMap[Repository\Conversation::class]
            ->allows('fetchUsingId')
            ->andReturn($defaultMockConversation)
            ->byDefault();
        $this->repoMap[Repository\Conversation::class]
            ->allows('save')
            ->byDefault();

        parent::setUp();

        $this->commandHandler->allows('handleCommand')
                             ->andReturn(new Result())
                             ->byDefault();
    }

    public function testHandleMarksConversationAsClosed(): void
    {
        $command = CloseConversationCommand::create(['id' => 1]);

        $this->repoMap[Repository\Conversation::class]
            ->expects('save')
            ->with(
                m::on(
                    function ($conversation) {
                        $this->assertTrue($conversation->getIsClosed());
                        return true;
                    },
                ),
            );

        $this->sut->handleCommand($command);
    }

    public function testHandleMarksTaskAsClosed(): void
    {
        $command = CloseConversationCommand::create($commandParameters = ['id' => 1]);

        $this->expectedSideEffect(CloseTasks::class, [], new Result(), 1);

        $this->sut->handleCommand($command);
    }

    public function testHandleGeneratesAndStoresSnapshot(): void
    {
        $command = CloseConversationCommand::create(['id' => 1]);

        $this->expectedSideEffect(StoreSnapshot::class, [], new Result(), 1);

        $this->sut->handleCommand($command);
    }

    public function testHandleGeneratesAndStoresEnhancedSnapshot(): void
    {
        $command = CloseConversationCommand::create(['id' => 1]);

        $this->expectedSideEffect(StoreEnhancedSnapshot::class, [], new Result(), 1);

        $this->sut->handleCommand($command);
    }

    public function testHandleCreatesCorrespondenceRecord(): void
    {
        $command = CloseConversationCommand::create(['id' => 1]);

        $this->expectedSideEffect(CreateCorrespondenceRecord::class, [], new Result(), 1);

        $this->sut->handleCommand($command);
    }
}
