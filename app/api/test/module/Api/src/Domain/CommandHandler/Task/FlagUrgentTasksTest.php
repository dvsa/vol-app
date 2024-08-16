<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Task;

use Dvsa\Olcs\Transfer\Command\Task\FlagUrgentTasks as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Task\FlagUrgentTasks;
use Dvsa\Olcs\Api\Domain\Repository\Task;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * FlagUrgentTasksTest
 */
class FlagUrgentTasksTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new FlagUrgentTasks();
        $this->mockRepo('Task', Task::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $command = Cmd::create([]);

        $this->repoMap['Task']->shouldReceive('flagUrgentsTasks')->with()->once()->andReturn(73);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '73 task(s) flagged as urgent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
