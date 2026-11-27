<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UndoGrant;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UndoGrant as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UndoCancelAllInterimFees as UndoCmd;

/**
 * Grant Goods Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UndoGrantTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UndoGrant();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('Task', \Dvsa\Olcs\Api\Domain\Repository\Task::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_STATUS_UNDER_CONSIDERATION,
            ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION,
            Category::CATEGORY_APPLICATION => m::mock(RefData::class),
            Category::TASK_SUB_CATEGORY_APPLICATION_GRANT_FEE_DUE => m::mock(RefData::class),
        ];
        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        $task = $this->newGrantTask('N');
        $closedTask = $this->newGrantTask('Y');

        $tasks = new ArrayCollection([$closedTask, $task]);

        /** @var Fee $fee */
        $fee = m::mock(Fee::class)
            ->shouldReceive('isGrantFee')
            ->andReturn(true)
            ->shouldReceive('isOutstanding')
            ->andReturn(true)
            ->shouldReceive('isFullyOutstanding')
            ->andReturn(true)
            ->shouldReceive('getId')
            ->andReturn(333)
            ->getMock();

        $fees = new ArrayCollection();
        $fees->add($fee);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->setTasks($tasks);
        $application
            ->shouldReceive('getFees')
            ->andReturn($fees);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->repoMap['Task']->shouldReceive('save')
            ->once()
            ->with($task);

        $result1 = new Result();
        $result1->addMessage('CancelLicenceFees');
        $this->expectedSideEffect(CancelFee::class, ['id' => 333], $result1);

        $result2 = new Result();
        $result2->addMessage('UndoCancelAllInterimFees');
        $this->expectedSideEffect(UndoCmd::class, ['id' => 111], $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CancelLicenceFees',
                'UndoCancelAllInterimFees',
                '1 Task(s) closed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(Licence::LICENCE_STATUS_UNDER_CONSIDERATION, $licence->getStatus()->getId());
        $this->assertEquals(
            ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION,
            $application->getStatus()->getId()
        );

        $this->assertEquals('Y', $task->getIsClosed());
        $this->assertEquals('Y', $closedTask->getIsClosed());
    }

    public function testHandleCommandDoesNotCloseALoadedTaskThatHoldsCategoryEntities(): void
    {
        $command = Cmd::create(['id' => 111]);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        $task = new Task(new Category(), new SubCategory());
        $task->setIsClosed('N');

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->setTasks(new ArrayCollection([$task]));
        $application->shouldReceive('getFees')->andReturn(new ArrayCollection());

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->repoMap['Task']->shouldReceive('save')->never();

        $this->expectedSideEffect(UndoCmd::class, ['id' => 111], new Result());

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['0 Task(s) closed'], $result->toArray()['messages']);
        $this->assertEquals('N', $task->getIsClosed());
    }

    private function newGrantTask(string $isClosed): Task
    {
        $task = new Task(new Category(), new SubCategory());
        $task->setCategory($this->refData[Category::CATEGORY_APPLICATION]);
        $task->setSubCategory($this->refData[Category::TASK_SUB_CATEGORY_APPLICATION_GRANT_FEE_DUE]);
        $task->setIsClosed($isClosed);

        return $task;
    }
}
