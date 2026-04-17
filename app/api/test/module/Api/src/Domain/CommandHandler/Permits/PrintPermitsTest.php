<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use Dvsa\Olcs\Api\Domain\Command\Permits\ProceedToStatus;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\PrintPermits as PrintPermitsHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Queue as QueueRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\Permits\PrintPermits as PrintPermitsCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Print Permits Test
 */
class PrintPermitsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PrintPermitsHandler();
        $this->mockRepo('Queue', QueueRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
            'config' => [
                'permit_printing' => ['max_batch_size' => null]
            ],
        ];

        parent::setUp();
    }

    public function testHandleMaxBatchSizeReached(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('ERR_PERMIT_PRINTING_MAX_BATCH_SIZE_REACHED');

        $cmdData = [
            'ids' => range(1, PrintPermitsHandler::MAX_BATCH_SIZE + 1),
        ];

        $command = PrintPermitsCmd::create($cmdData);

        $this->sut->handleCommand($command);
    }

    public function testHandleMaxBatchSizeReachedConfig(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('ERR_PERMIT_PRINTING_MAX_BATCH_SIZE_REACHED');

        $maxBatchSize = 10000;

        $this->mockedSmServices['config'] = ['permit_printing' => ['max_batch_size' => $maxBatchSize]];

        $cmdData = [
            'ids' => range(1, $maxBatchSize + 1),
        ];

        $command = PrintPermitsCmd::create($cmdData);

        $this->sut->handleCommand($command);
    }

    public function testHandlePrintingAlreadyInProgress(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $this->expectExceptionMessage('ERR_PERMIT_PRINTING_ALREADY_IN_PROGRESS');

        $cmdData = [
            'ids' => range(1, PrintPermitsHandler::MAX_BATCH_SIZE),
        ];

        $command = PrintPermitsCmd::create($cmdData);

        $this->repoMap['Queue']
            ->shouldReceive('isItemInQueue')
            ->with(
                [Queue::TYPE_PERMIT_GENERATE, Queue::TYPE_PERMIT_PRINT],
                [Queue::STATUS_QUEUED, Queue::STATUS_PROCESSING]
            )
            ->andReturn(true);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $userId = 1;
        $p1Id = 10;
        $p2Id = 11;

        $cmdData = [
            'ids' => [$p1Id, $p2Id],
        ];

        $command = PrintPermitsCmd::create($cmdData);

        $this->repoMap['Queue']
            ->shouldReceive('isItemInQueue')
            ->with(
                [Queue::TYPE_PERMIT_GENERATE, Queue::TYPE_PERMIT_PRINT],
                [Queue::STATUS_QUEUED, Queue::STATUS_PROCESSING]
            )
            ->andReturn(false);

        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId($userId);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $data = [
            'type' => Queue::TYPE_PERMIT_GENERATE,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode(
                [
                    'ids' => [$p1Id, $p2Id],
                    'user' => $userId
                ]
            )
        ];
        $this->expectedSideEffect(
            CreatQueue::class,
            $data,
            (new Result())->addMessage('Queue item created')
        );

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => [$p1Id, $p2Id],
                'status' => IrhpPermit::STATUS_AWAITING_PRINTING,
            ],
            (new Result())->addMessage('Permits updated')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Permits updated',
                'Queue item created',
                'Permits submitted for printing',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
