<?php

namespace Dvsa\OlcsTest\Api\Service\Permits\Checkable;

use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepository;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Service\Permits\Checkable\CheckableApplicationInterface;
use Dvsa\Olcs\Api\Service\Permits\Checkable\CheckedValueUpdater;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CheckedValueUpdaterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CheckedValueUpdaterTest extends MockeryTestCase
{
    private $checkableApplication;

    private $taskRepo;

    private $checkedValueUpdater;

    public function setUp(): void
    {
        $this->checkableApplication = m::mock(CheckableApplicationInterface::class);

        $this->taskRepo = m::mock(TaskRepository::class);

        $this->checkedValueUpdater = new CheckedValueUpdater($this->taskRepo);
    }

    /**
     * @dataProvider dpCheckNotRequired
     */
    public function testCheckNotRequired($checked)
    {
        $this->checkableApplication->shouldReceive('requiresPreAllocationCheck')
            ->withNoArgs()
            ->andReturn(false);

        $this->checkedValueUpdater->updateIfRequired($this->checkableApplication, $checked);
    }

    public function dpCheckNotRequired()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testCheckRequiredNotChecked()
    {
        $checked = false;

        $this->checkableApplication->shouldReceive('requiresPreAllocationCheck')
            ->withNoArgs()
            ->andReturn(true);
        $this->checkableApplication->shouldReceive('updateChecked')
            ->with($checked)
            ->once();

        $this->checkedValueUpdater->updateIfRequired($this->checkableApplication, $checked);
    }

    public function testCheckRequiredAndCheckedTaskFound()
    {
        $checked = true;

        $task = m::mock(Task::class);
        $task->shouldReceive('setIsClosed')
            ->with('Y')
            ->once()
            ->globally()
            ->ordered();

        $this->taskRepo->shouldReceive('save')
            ->with($task)
            ->once()
            ->globally()
            ->ordered();

        $this->checkableApplication->shouldReceive('requiresPreAllocationCheck')
            ->withNoArgs()
            ->andReturn(true);
        $this->checkableApplication->shouldReceive('updateChecked')
            ->with($checked)
            ->once();
        $this->checkableApplication->shouldReceive('fetchOpenSubmissionTask')
            ->withNoArgs()
            ->andReturn($task);

        $this->checkedValueUpdater->updateIfRequired($this->checkableApplication, $checked);
    }

    public function testCheckRequiredAndCheckedTaskNotFound()
    {
        $checked = true;

        $this->checkableApplication->shouldReceive('requiresPreAllocationCheck')
            ->withNoArgs()
            ->andReturn(true);
        $this->checkableApplication->shouldReceive('updateChecked')
            ->with($checked)
            ->once();
        $this->checkableApplication->shouldReceive('fetchOpenSubmissionTask')
            ->withNoArgs()
            ->andReturn(null);

        $this->checkedValueUpdater->updateIfRequired($this->checkableApplication, $checked);
    }
}
