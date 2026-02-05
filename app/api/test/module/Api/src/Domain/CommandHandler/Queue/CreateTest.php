<?php

declare(strict_types=1);

/**
 * Queue Create Command Handler Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Queue;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Queue\Create;
use Dvsa\Olcs\Api\Domain\Repository\Queue as Repo;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;

/**
 * Queue Create Command Handler Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Create();
        $this->mockRepo('Queue', Repo::class);

        $this->refData = [
            QueueEntity::STATUS_QUEUED,
            QueueEntity::TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER
        ];
        $this->references = [
            UserEntity::class => [
                1 => m::mock(UserEntity::class)
            ]
        ];
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];
        parent::setUp();
    }

    /**
     * Test handleCommand method
     */
    public function testHandleCommand(): void
    {
        $this->mockAuthService();

        $processAfterDate = '2015-12-25 04:30:00';
        $processAfterDateTime = new \DateTime($processAfterDate);

        $command = Cmd::create(
            [
                'status' => QueueEntity::STATUS_QUEUED,
                'type' => QueueEntity::TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER,
                'entityId' => 1,
                'options' => '{"foo":"bar"}',
                'processAfterDate' => $processAfterDate,
            ]
        );

        /** @var QueueEntity $savedQueue */
        $savedQueue = null;
        $this->repoMap['Queue']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(QueueEntity::class))
            ->andReturnUsing(
                function (QueueEntity $queue) use (&$savedQueue) {
                    $queue->setId(1);
                    $savedQueue = $queue;
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Queue created'], $result->getMessages());
        $this->assertEquals(1, $result->getId('queue'));
        $this->assertEquals($savedQueue->getId(), 1);
        $this->assertEquals(
            $savedQueue->getType(),
            $this->refData[QueueEntity::TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER]
        );
        $this->assertEquals(
            $savedQueue->getStatus(),
            $this->refData[QueueEntity::STATUS_QUEUED]
        );
        $this->assertEquals(
            $savedQueue->getOptions(),
            '{"foo":"bar"}'
        );
        $this->assertEquals(
            $savedQueue->getProcessAfterDate(),
            $processAfterDateTime
        );
    }

    protected function mockAuthService(): void
    {
        /** @var User $mockUser */
        $mockUser = m::mock(UserEntity::class)->makePartial();
        $mockUser->setId(1);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);
    }
}
