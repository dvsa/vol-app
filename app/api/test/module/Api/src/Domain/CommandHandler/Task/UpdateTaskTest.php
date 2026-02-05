<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Task;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Task\UpdateTask;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\Task\UpdateTask as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Task\UpdateTask::class)]
class UpdateTaskTest extends AbstractCommandHandlerTestCase
{
    public const TASK_ID = 999;

    public const TEAM_ID = 7001;
    public const TEAM_ID_2 = 7002;

    public const USER_ID = 6001;

    public const CAT_ID = 5001;
    public const SUB_CAT_ID = 4001;

    /** @var UpdateTask */
    protected $sut;
    /** @var  m\MockInterface | Team */
    private $mockTeam;
    /** @var  m\MockInterface | Team */
    private $mockTeam2;
    /** @var  m\MockInterface | User */
    private $mockUser;

    public function setUp(): void
    {
        $this->sut = new UpdateTask();

        $this->mockRepo('Task', m::mock(Repository\Task::class)->makePartial());

        $this->mockTeam = m::mock(Team::class);
        $this->mockTeam2 = m::mock(Team::class);

        $this->mockUser = m::mock(User::class);
        $this->mockUser->shouldReceive('getTeam')->andReturn($this->mockTeam2);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            User::class => [
                self::USER_ID => $this->mockUser,
            ],
            Team::class => [
                self::TEAM_ID => $this->mockTeam,
                self::TEAM_ID_2 => $this->mockTeam2,
            ],
        ];

        $this->categoryReferences = [
            self::CAT_ID => m::mock(Category::class)
        ];

        $this->subCategoryReferences = [
            self::SUB_CAT_ID => m::mock(SubCategory::class)
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 123,
            'version' => 1,
            'description' => 'foo bar',
            'actionDate' => '2015-01-01',
            'urgent' => 'Y',
            'category' => self::CAT_ID,
            'subCategory' => self::SUB_CAT_ID,
            'assignedToUser' => self::USER_ID,
            'assignedToTeam' => self::TEAM_ID,
        ];

        $command = Cmd::create($data);

        /** @var \Dvsa\Olcs\Api\Entity\Task\Task $task */
        $task = m::mock(\Dvsa\Olcs\Api\Entity\Task\Task::class)->makePartial();

        $this->repoMap['Task']
            ->shouldReceive('fetchUsingId')->with($command, Query::HYDRATE_OBJECT, 1)->andReturn($task)
            ->shouldReceive('save')->with($task);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Task updated'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('foo bar', $task->getDescription());
        $this->assertEquals('2015-01-01', $task->getActionDate()->format('Y-m-d'));
        $this->assertEquals('Y', $task->getUrgent());
        $this->assertSame($this->categoryReferences[self::CAT_ID], $task->getCategory());
        $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $task->getSubCategory());
        $this->assertSame($this->mockUser, $task->getAssignedToUser());
        $this->assertSame($this->mockTeam, $task->getAssignedToTeam());
    }

    public function testHandleCommandUnassignedUser(): void
    {
        $data = [
            'id' => 123,
            'version' => 1,
            'description' => 'foo bar',
            'actionDate' => '2015-01-01',
            'urgent' => 'Y',
            'category' => self::CAT_ID,
            'subCategory' => self::SUB_CAT_ID,
            'assignedToTeam' => self::TEAM_ID,
        ];

        $command = Cmd::create($data);

        /** @var \Dvsa\Olcs\Api\Entity\Task\Task $task */
        $task = m::mock(\Dvsa\Olcs\Api\Entity\Task\Task::class)->makePartial();

        $this->repoMap['Task']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($task)
            ->shouldReceive('save')
            ->with($task);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Task updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('foo bar', $task->getDescription());
        $this->assertEquals('2015-01-01', $task->getActionDate()->format('Y-m-d'));
        $this->assertEquals('Y', $task->getUrgent());
        $this->assertSame($this->categoryReferences[self::CAT_ID], $task->getCategory());
        $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $task->getSubCategory());
        $this->assertNull($task->getAssignedToUser());
        $this->assertSame($this->mockTeam, $task->getAssignedToTeam());
    }

    public function testHandleCommandTeamFromUser(): void
    {
        $command = Cmd::create(
            [
                'id' => self::TASK_ID,
                'assignedToUser' => self::USER_ID,
                'version' => 1,
            ]
        );

        /** @var \Dvsa\Olcs\Api\Entity\Task\Task $task */
        $task = m::mock(\Dvsa\Olcs\Api\Entity\Task\Task::class)->makePartial();

        $this->repoMap['Task']
            ->shouldReceive('fetchUsingId')->with($command, Query::HYDRATE_OBJECT, 1)->andReturn($task)
            ->shouldReceive('save')->with($task);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Task updated',
            ],
        ];
        static::assertEquals($expected, $result->toArray());

        static::assertSame($this->mockUser, $task->getAssignedToUser());
        static::assertSame($this->mockTeam2, $task->getAssignedToTeam());
    }

    public function testHandleCommandFailTeamInvalid(): void
    {
        $this->expectException(ValidationException::class);

        $this->sut->handleCommand(
            Cmd::create(
                [
                    'id' => self::TASK_ID,
                ]
            )
        );
    }
}
