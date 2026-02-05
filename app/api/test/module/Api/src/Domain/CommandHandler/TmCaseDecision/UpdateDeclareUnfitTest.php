<?php

declare(strict_types=1);

/**
 * Update DeclareUnfit Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmCaseDecision;

use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision\UpdateDeclareUnfit;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;
use Dvsa\Olcs\Api\Domain\Repository\TmCaseDecision as TmCaseDecisionRepo;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as TmCaseDecisionEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Command\TmCaseDecision\UpdateDeclareUnfit as Cmd;
use Mockery as m;

/**
 * Update DeclareUnfit Test
 */
class UpdateDeclareUnfitTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateDeclareUnfit();
        $this->mockRepo('TmCaseDecision', TmCaseDecisionRepo::class);
        $this->mockRepo('Task', TaskRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'unfitnessReason',
            'rehabMeasure',
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'unfitnessStartDate' => '2016-02-01',
            'unfitnessEndDate' => '2016-02-01',
            'unfitnessReasons' => ['unfitnessReason'],
            'rehabMeasures' => ['rehabMeasure'],
        ];

        $taskId = 99;
        $transportManagerId = 44;

        $command = Cmd::create($data);

        $decision = new RefData();
        $decision->setId(TmCaseDecisionEntity::DECISION_DECLARE_UNFIT);

        $task = m::mock(Task::class);
        $task->shouldReceive('getActionDate')
            ->andReturn('01-05-2005')
            ->shouldReceive('setActionDate')
            ->with($data['unfitnessStartDate'])
            ->shouldReceive('getId')
            ->andReturn($taskId);

        $transportManager = m::mock(TransportManager::class)->makePartial();
        $transportManager->setId($transportManagerId);

        $case = m::mock(Cases::class);
        $case->shouldReceive('getTransportManager')
            ->once()
            ->andReturn($transportManager);

        /** @var TmCaseDecisionEntity $tmCaseDecision */
        $tmCaseDecision = m::mock(TmCaseDecisionEntity::class)->makePartial();
        $tmCaseDecision->setId(111);
        $tmCaseDecision->setDecision($decision);
        $tmCaseDecision->shouldReceive('update')
            ->once()
            ->andReturnSelf();
        $tmCaseDecision->shouldReceive('getCase')
            ->once()
            ->andReturn($case);

        $this->repoMap['TmCaseDecision']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($tmCaseDecision);

        $this->repoMap['Task']->shouldReceive('fetchForTmCaseDecision')
            ->once()
            ->with($case, $transportManager, SubCategory::TM_SUB_CATEGORY_DECLARED_UNFIT)
            ->andReturn($task)
            ->shouldReceive('save')
            ->with($task);

        $this->repoMap['TmCaseDecision']->shouldReceive('save')
            ->once()
            ->with(m::type(TmCaseDecisionEntity::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'tmCaseDecision' => 111,
                'task' => $taskId,
            ],
            'messages' => [
                'Decision updated successfully',
                'Task action date updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandThrowsIncorrectActionException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $data = [
            'id' => 111,
            'version' => 1,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'unfitnessStartDate' => '2016-02-01',
            'unfitnessEndDate' => '2016-02-01',
            'unfitnessReasons' => ['unfitnessReason'],
            'rehabMeasures' => ['rehabMeasure'],
        ];

        $transportManagerId = 44;

        $command = Cmd::create($data);

        $decision = new RefData();
        $decision->setId(TmCaseDecisionEntity::DECISION_REPUTE_NOT_LOST);

        $transportManager = m::mock(TransportManager::class)->makePartial();
        $transportManager->setId($transportManagerId);

        $case = m::mock(Cases::class);
        $case->shouldReceive('getTransportManager')
            ->once()
            ->andReturn($transportManager);

        /** @var TmCaseDecisionEntity $tmCaseDecision */
        $tmCaseDecision = m::mock(TmCaseDecisionEntity::class)->makePartial();
        $tmCaseDecision->setId(111);
        $tmCaseDecision->setDecision($decision);
        $tmCaseDecision->shouldReceive('getCase')
            ->once()
            ->andReturn($case);

        $this->repoMap['TmCaseDecision']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($tmCaseDecision);

        $this->sut->handleCommand($command);
    }
}
