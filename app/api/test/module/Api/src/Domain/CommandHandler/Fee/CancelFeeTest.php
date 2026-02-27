<?php

declare(strict_types=1);

/**
 * CancelFeeTestt
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Repository\Fee;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks as CloseTasksCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * CancelFeeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CancelFeeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CancelFee();
        $this->mockRepo('Fee', Fee::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            FeeEntity::STATUS_CANCELLED
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = CancelFeeCommand::create(['id' => 863]);

        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setId(863);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($fee);
        $this->repoMap['Fee']->shouldReceive('save')->with($fee)->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Fee 863 cancelled successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->mapRefData(FeeEntity::STATUS_CANCELLED), $fee->getFeeStatus());
    }

    public function testHandleCommandWithTask(): void
    {
        $command = CancelFeeCommand::create(['id' => 863]);

        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setId(863);

        $task = m::mock(TaskEntity::class);
        $task->shouldReceive('getId')->andReturn(99);
        $fee->setTask($task);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($fee);
        $this->repoMap['Fee']->shouldReceive('save')->with($fee)->once();

        $taskResult = new Result();
        $taskResult->addMessage('Task 99 closed');
        $this->expectedSideEffect(
            CloseTasksCmd::class,
            ['ids' => [99]],
            $taskResult
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Fee 863 cancelled successfully',
                'Task 99 closed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->mapRefData(FeeEntity::STATUS_CANCELLED), $fee->getFeeStatus());
    }
}
