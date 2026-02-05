<?php

declare(strict_types=1);

/**
 * Delete Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Team;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Team\DeleteTeam as DeleteTeam;
use Dvsa\Olcs\Api\Domain\Repository\Team as TeamRepo;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Team\DeleteTeam as Cmd;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Delete Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteTeam();
        $this->mockRepo('Team', TeamRepo::class);
        $this->mockRepo('User', UserRepo::class);
        $this->mockRepo('Task', TaskRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            TrafficAreaEntity::class => [
                5 => m::mock(TrafficAreaEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandNeedReassign(): void
    {
        $command = Cmd::create(['id' => 1]);

        $mockTeam = m::mock()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getTaskAllocationRules')
            ->andReturn([])
            ->once()
            ->shouldReceive('getTasks')
            ->andReturn(['tasks'])
            ->times(3)
            ->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($mockTeam)
            ->getMock();

        $this->repoMap['User']
            ->shouldReceive('fetchUsersCountByTeam')
            ->with(1)
            ->andReturn(0)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['tasks' => 1],
            'messages' => ['Need to reassign 1 task(s)']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithValidate(): void
    {
        $command = Cmd::create(['id' => 1, 'validate' => true]);

        $mockTeam = m::mock()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getTaskAllocationRules')
            ->andReturn([])
            ->once()
            ->shouldReceive('getTasks')
            ->andReturn([])
            ->once()
            ->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($mockTeam)
            ->getMock();

        $this->repoMap['User']
            ->shouldReceive('fetchUsersCountByTeam')
            ->with(1)
            ->andReturn(0)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'messages' => ['Ready to remove'],
            'id' => []
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithReassignTasks(): void
    {
        $command = Cmd::create(['id' => 1, 'validate' => false, 'newTeam' => 2]);

        $mockNewTeam = m::mock()
            ->shouldReceive('addTasks')
            ->once()
            ->getMock();

        $mockTask = m::mock()
            ->shouldReceive('setAssignedToTeam')
            ->with($mockNewTeam)
            ->once()
            ->getMock();

        $tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $tasks->add($mockTask);

        $mockTeam = m::mock()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->twice()
            ->shouldReceive('getTaskAllocationRules')
            ->andReturn([])
            ->once()
            ->shouldReceive('getTasks')
            ->andReturn($tasks)
            ->times(4)
            ->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($mockTeam)
            ->shouldReceive('delete')
            ->with($mockTeam)
            ->once()
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn($mockNewTeam)
            ->once()
            ->shouldReceive('save')
            ->with($mockTeam)
            ->once()
            ->shouldReceive('save')
            ->with($mockNewTeam)
            ->once()
            ->getMock();

        $this->repoMap['User']
            ->shouldReceive('fetchUsersCountByTeam')
            ->with(1)
            ->andReturn(0)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'messages' => ['1 task(s) reassigned', 'Team deleted successfully'],
            'id' => ['team' => 1]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithVaidationException(): void
    {
        $this->expectException(ValidationException::class);

        $command = Cmd::create(['id' => 1]);

        $mockTeam = m::mock()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getTaskAllocationRules')
            ->andReturn(['taskAllocationRules'])
            ->once()
            ->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($mockTeam)
            ->getMock();

        $this->repoMap['User']
            ->shouldReceive('fetchUsersCountByTeam')
            ->with(1)
            ->andReturn(1)
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
