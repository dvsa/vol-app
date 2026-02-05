<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Queue;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Queue\Failed;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Queue\Failed::class)]
class FailedTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Failed();
        $this->mockRepo('Queue', Repository\Queue::class);

        $this->refData = [
            QueueEntity::STATUS_FAILED,
        ];

        parent::setUp();
    }

    /**
     * Test handleCommand method
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommand')]
    public function testHandleCommand(mixed $lastErr, mixed $existingLastErr, mixed $expectedLastErr): void
    {
        $id = 1234;

        $mockQueueEntity = new QueueEntity();
        $mockQueueEntity->setId($id);
        $mockQueueEntity->setLastError($existingLastErr);

        $command = DomainCmd\Queue\Failed::create(
            [
                'item' => $mockQueueEntity,
                'lastError' => $lastErr,
            ]
        );

        $this->repoMap['Queue']
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (QueueEntity $entity) use ($id, $expectedLastErr) {
                    static::assertEquals($id, $entity->getId());
                    static::assertEquals($expectedLastErr, $entity->getLastError());
                    static::assertEquals(QueueEntity::STATUS_FAILED, $entity->getStatus()->getId());
                }
            );

        $this->expectedSideEffect(DomainCmd\Queue\Delete::class, ['id' => $id], new Result());

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Queue item marked failed'], $result->getMessages());

        $this->assertEquals(QueueEntity::STATUS_FAILED, $mockQueueEntity->getStatus()->getId());
    }

    public static function dpTestHandleCommand(): array
    {
        return [
            'maximum attempts error' => [
                'lastErr' => QueueEntity::ERR_MAX_ATTEMPTS,
                'existingLastErr' => null,
                'expectedLastErr' => QueueEntity::ERR_MAX_ATTEMPTS . ':',
            ],
            'maximum attempts error with existing error' => [
                'lastErr' => QueueEntity::ERR_MAX_ATTEMPTS,
                'existingLastErr' => 'existing error',
                'expectedLastErr' => QueueEntity::ERR_MAX_ATTEMPTS . ': existing error',
            ],
            'other error' => [
                'lastErr' => 'unit_LastErr',
                'existingLastErr' => null,
                'expectedLastErr' => 'unit_LastErr',
            ],
            'other error with existing error' => [
                'lastErr' => 'unit_LastErr',
                'existingLastErr' => 'existing error',
                'expectedLastErr' => 'unit_LastErr',
            ],
        ];
    }
}
