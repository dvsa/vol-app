<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention\DeleteEntities;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\DataRetention\DeleteEntities as Cmd;

/**
 * Class DeleteEntitiesTest
 */
class DeleteEntitiesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteEntities();
        $this->mockRepo('DataRetention', Repository\DataRetention::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);
        $this->mockRepo('Queue', Repository\Queue::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['limit' => 9]);

        $this->repoMap['SystemParameter']->shouldReceive('getDisableDataRetentionDelete')
            ->with()->once()->andReturn(false);
        $this->repoMap['SystemParameter']->shouldReceive('getSystemDataRetentionUser')->with()->once()->andReturn(34);
        $this->repoMap['DataRetention']->shouldReceive('runCleanupProc')->with(9, 34)->once();
        $this->repoMap['Queue']->shouldReceive('isItemTypeQueued')->with(Queue::TYPE_REMOVE_DELETED_DOCUMENTS)->once()
            ->andReturn(true);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandThrowException(): void
    {
        $command = Cmd::create(['limit' => 9]);
        $exception = new \Exception('error');

        $this->repoMap['SystemParameter']->shouldReceive('getDisableDataRetentionDelete')
            ->with()->once()->andReturn(false);
        $this->repoMap['SystemParameter']->shouldReceive('getSystemDataRetentionUser')->with()->once()->andReturn(34);
        $this->repoMap['DataRetention']->shouldReceive('runCleanupProc')->with(9, 34)->once()->andThrow($exception);

        $this->expectException(\Exception::class);
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandQueueDeleteDocuments(): void
    {
        $command = Cmd::create(['limit' => 9]);

        $this->repoMap['SystemParameter']->shouldReceive('getDisableDataRetentionDelete')
            ->with()->once()->andReturn(false);
        $this->repoMap['SystemParameter']->shouldReceive('getSystemDataRetentionUser')->with()->once()->andReturn(34);
        $this->repoMap['DataRetention']->shouldReceive('runCleanupProc')->with(9, 34)->once();
        $this->repoMap['Queue']->shouldReceive('isItemTypeQueued')->with(Queue::TYPE_REMOVE_DELETED_DOCUMENTS)->once()
            ->andReturn(false);

        $this->expectedSideEffect(
            Create::class,
            ['type' => Queue::TYPE_REMOVE_DELETED_DOCUMENTS, 'status' => Queue::STATUS_QUEUED],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandSystemParamLimit(): void
    {
        $command = Cmd::create([]);

        $this->repoMap['SystemParameter']->shouldReceive('getDisableDataRetentionDelete')
            ->with()->once()->andReturn(false);
        $this->repoMap['SystemParameter']->shouldReceive('getDataRetentionDeleteLimit')->with()->once()->andReturn(54);
        $this->repoMap['SystemParameter']->shouldReceive('getSystemDataRetentionUser')->with()->once()->andReturn(34);
        $this->repoMap['DataRetention']->shouldReceive('runCleanupProc')->with(54, 34)->once();
        $this->repoMap['Queue']->shouldReceive('isItemTypeQueued')->with(Queue::TYPE_REMOVE_DELETED_DOCUMENTS)->once()
            ->andReturn(true);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandDisabled(): void
    {
        $command = Cmd::create([]);

        $this->repoMap['SystemParameter']->shouldReceive('getDisableDataRetentionDelete')->with()->once()
            ->andReturn(true);

        $this->expectException(BadRequestException::class);
        $this->sut->handleCommand($command);
    }
}
