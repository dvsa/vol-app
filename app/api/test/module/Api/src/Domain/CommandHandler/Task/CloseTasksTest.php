<?php

declare(strict_types=1);

/**
 * Close Tasks Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Task;

use Dvsa\Olcs\Transfer\Command\Task\CloseTasks as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Task\CloseTasks;
use Dvsa\Olcs\Api\Domain\Repository\Task;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Close Tasks Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CloseTasksTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CloseTasks();
        $this->mockRepo('Task', Task::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['ids' => [123, 321]]);

        $task1 = m::mock(\Dvsa\Olcs\Api\Entity\Task\Task::class);
        $task1->shouldReceive('setIsClosed')->once()->with('Y');
        $task2 = m::mock(\Dvsa\Olcs\Api\Entity\Task\Task::class);
        $task2->shouldReceive('setIsClosed')->once()->with('Y');

        $this->repoMap['Task']->shouldReceive('fetchById')
            ->with(123)
            ->andReturn($task1)
            ->shouldReceive('fetchById')
            ->with(321)
            ->andReturn($task2)
            ->shouldReceive('save')
            ->with($task1)
            ->shouldReceive('save')
            ->with($task2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '2 Task(s) closed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
