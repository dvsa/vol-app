<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Task;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule as TaskAllocationRuleEntity;
use Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Task\CreateTask::class)]
class CreateTaskTest extends AbstractCommandHandlerTestCase
{
    /** @var  CreateTask */
    protected $sut;

    protected $mockLicence;

    protected $mockApplication;

    protected $rules;
    /** @var  array */
    protected $rulesForAlphaSplit;
    /** @var  m\MockInterface | TaskAllocationRuleEntity */
    protected $ruleForAlphaSplit;

    /**
     * Set up
     */
    public function setUp(): void
    {
        $this->sut = new CreateTask();

        $this->mockRepo(Repository\Task::class, m::mock(Repository\Task::class)->makePartial());
        $this->mockRepo(Repository\TaskAllocationRule::class, Repository\TaskAllocationRule::class);
        $this->mockRepo(Repository\SystemParameter::class, Repository\SystemParameter::class);

        parent::setUp();

        /** @var TaskAllocationRuleEntity $rule */
        $rule = m::mock(TaskAllocationRuleEntity::class)->makePartial();
        $rule->setUser($this->references[User::class][888]);
        $rule->setTeam($this->references[Team::class][999]);

        $this->rules = [
            $rule
        ];

        /** @var TaskAllocationRuleEntity $rule */
        $this->ruleForAlphaSplit = m::mock(TaskAllocationRuleEntity::class)->makePartial();
        $this->ruleForAlphaSplit->setTeam($this->references[Team::class][999]);

        $this->rulesForAlphaSplit = [
            $this->ruleForAlphaSplit
        ];
    }

    /**
     * Init references
     */
    #[\Override]
    protected function initReferences(): void
    {
        $this->categoryReferences = [
            1 => m::mock(Category::class)
        ];

        $this->subCategoryReferences = [
            2 => m::mock(SubCategory::class)
        ];

        $this->mockLicence = m::mock(Licence::class);
        $this->mockApplication = m::mock(Application::class);

        $this->references = [
            User::class => [
                11 => m::mock(User::class),
                888 => m::mock(User::class),
                999 => m::mock(User::class),
            ],
            Team::class => [
                22 => m::mock(Team::class),
                999 => m::mock(Team::class)
            ],
            Application::class => [
                111 => $this->mockApplication
            ],
            Licence::class => [
                222 => $this->mockLicence
            ],
            BusReg::class => [
                64 => m::mock(BusReg::class)
            ],
            Cases::class => [
                164 => m::mock(Cases::class)
            ],
            TransportManager::class => [
                264 => m::mock(TransportManager::class)
            ],
            Organisation::class => [
                364 => m::mock(Organisation::class)
            ],
            Submission::class => [
                765 => m::mock(Cases::class)
            ],
            IrhpApplication::class => [
                979 => m::mock(IrhpApplication::class)
            ],
            Surrender::class => [
                765 => m::mock(Surrender::class)
            ],
        ];

        parent::initReferences();
    }

    /**
     * Test handle command
     */
    public function testHandleCommandX(): void
    {
        $data = [
            'category' => 1,
            'subCategory' => 2,
            'assignedToUser' => 11,
            'assignedToTeam' => 22,
            'application' => 111,
            'licence' => 222,
            'actionDate' => '2015-01-01',
            'description' => 'Some task',
            'isClosed' => false,
            'urgent' => false,
            'busReg' => 64,
            'case' => 164,
            'surrender' => 765,
            'transportManager' => 264,
            'irfoOrganisation' => 364,
            'irhpApplication' => 979,
            'submission' => 765,
            'assignedByUser' => 999,
        ];

        $command = Cmd::create($data);

        $this->repoMap[Repository\Task::class]->shouldReceive('save')
            ->once()
            ->with(m::type(TaskEntity::class))
            ->andReturnUsing(
                function (TaskEntity $task) {
                    $task->setId(123);

                    $this->assertSame($this->categoryReferences[1], $task->getCategory());
                    $this->assertSame($this->subCategoryReferences[2], $task->getSubCategory());
                    $this->assertSame($this->references[User::class][11], $task->getAssignedToUser());
                    $this->assertSame($this->references[Team::class][22], $task->getAssignedToTeam());
                    $this->assertSame($this->references[Application::class][111], $task->getApplication());
                    $this->assertSame($this->references[Licence::class][222], $task->getLicence());

                    $this->assertSame($this->references[BusReg::class][64], $task->getBusReg());
                    $this->assertSame($this->references[Cases::class][164], $task->getCase());
                    $this->assertSame($this->references[Submission::class][765], $task->getSubmission());
                    $this->assertSame($this->references[TransportManager::class][264], $task->getTransportManager());
                    $this->assertSame($this->references[Organisation::class][364], $task->getIrfoOrganisation());
                    $this->assertSame($this->references[IrhpApplication::class][979], $task->getIrhpApplication());
                    $this->assertSame($this->references[Surrender::class][765], $task->getSurrender());

                    $this->assertEquals('2015-01-01', $task->getActionDate()->format('Y-m-d'));
                    $this->assertEquals('Some task', $task->getDescription());
                    $this->assertEquals(false, $task->getIsClosed());
                    $this->assertEquals(false, $task->getUrgent());

                    $this->assertEquals(null, $task->getCreatedBy());
                    $this->assertEquals(null, $task->getLastModifiedBy());
                    $this->assertEquals(new DateTime('now'), $task->getLastModifiedOn());

                    $this->assertSame($this->references[User::class][999], $task->getAssignedByUser());
                }
            );

        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 11],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with assign by category
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('rulesProvider')]
    public function testHandleCommandWithAssignByCategory(mixed $rules): void
    {
        $command = Cmd::create($this->getData(null));

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2)
            ->once()
            ->andReturn($rules);

        $this->repoMap[Repository\SystemParameter::class]->shouldReceive('fetchValue')
            ->with('task.default_team')
            ->once()
            ->andReturn(999)
            ->shouldReceive('fetchValue')
            ->with('task.default_user')
            ->once()
            ->andReturn(888);

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Rules provider
     *
     * @return array
     */
    public static function rulesProvider(): array
    {
        return [
            [
                []
            ],
            [
                [
                    'foo',
                    'bar'
                ]
            ]
        ];
    }

    /**
     * Test handle command with auto assignment with goods licence
     */
    public function testHandleCommandWithAutoAssignmentGoodsLicence(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence(Licence::LICENCE_CATEGORY_GOODS_VEHICLE, true);

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_GOODS_VEHICLE, 'B', false)
            ->once()
            ->andReturn($this->rules);

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with goods licence, rules not found
     */
    public function testHandleCommandWithAutoAssignmentGoodsLicenceRulesNotFound(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence(Licence::LICENCE_CATEGORY_GOODS_VEHICLE, true);

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_GOODS_VEHICLE, 'B', false)
            ->once()
            ->andReturn([])
            ->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_GOODS_VEHICLE, 'B')
            ->once()
            ->andReturn($this->rules)
            ->getMock();

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with PSV licence
     */
    public function testHandleCommandWithAutoAssignmentPsvLicence(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV, 'B')
            ->once()
            ->andReturn($this->rules);

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with PSV licence search rules by category and TA
     */
    public function testHandleCommandWithAutoAssignmentPsvLicenceSearchRulesByCategoryAndTa(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV, 'B')
            ->once()
            ->andReturn([])
            ->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, null, 'B')
            ->once()
            ->andReturn($this->rules);

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with PSV licence search rules by category and Operator Type
     */
    public function testHandleCommandWithAutoAssignmentPsvLicenceSearchRulesByCategoryAndOpType(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV, 'B')
            ->once()
            ->andReturn([])
            ->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, null, 'B')
            ->once()
            ->andReturn([])
            ->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV)
            ->once()
            ->andReturn($this->rules);

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with PSV licence search rules by category
     */
    public function testHandleCommandWithAutoAssignmentPsvLicenceSearchRulesByCategory(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV, 'B')
            ->once()
            ->andReturn([])
            ->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, null, 'B')
            ->once()
            ->andReturn([])
            ->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV)
            ->once()
            ->andReturn([])
            ->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2)
            ->once()
            ->andReturn($this->rules);

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with goods licence, alpha split / no user
     */
    public function testHandleCommandWithAutoAssignmentGoodsLicenceAlphaSplitNoUser(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence(Licence::LICENCE_CATEGORY_GOODS_VEHICLE, true);

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_GOODS_VEHICLE, 'B', false)
            ->once()
            ->andReturn($this->rulesForAlphaSplit);

        $this->mockSaveTask();
        $result = $this->sut->handleCommand($command);
        $expected = ['id' => ['task' => 123], 'messages' => ['Task created successfully']];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with goods licence, alpha split / no letter found
     */
    public function testHandleCommandWithAutoAssignmentGoodsLicenceAlphaSplitNoLetterFound(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence();

        $mockTaskAlphaSplit = m::mock(TaskAlphaSplit::class)->makePartial();
        $mockTaskAlphaSplit->setLetters('ABOC');
        $mockTaskAlphaSplit->setUser($this->references[User::class][888]);

        $taskAlphaSplits = new ArrayCollection();
        $taskAlphaSplits->add($mockTaskAlphaSplit);

        $this->mockLicence
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                ->shouldReceive('getType')
                ->andReturn(Organisation::ORG_TYPE_REGISTERED_COMPANY)
                ->once()
                ->shouldReceive('getName')
                ->andReturn('the 1company')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->ruleForAlphaSplit
            ->shouldReceive('getTaskAlphaSplits')
            ->andReturn($taskAlphaSplits)
            ->once()
            ->getMock();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV, 'B')
            ->once()
            ->andReturn($this->rulesForAlphaSplit);

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with goods licence, no alpha split found
     */
    public function testHandleCommandWithAutoAssignmentGoodsLicenceNoAlphaSplitFound(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence();
        $taskAlphaSplits = new ArrayCollection();

        $this->mockLicence
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getType')
                    ->andReturn(Organisation::ORG_TYPE_REGISTERED_COMPANY)
                    ->once()
                    ->shouldReceive('getName')
                    ->andReturn('company')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->ruleForAlphaSplit
            ->shouldReceive('getTaskAlphaSplits')
            ->andReturn($taskAlphaSplits)
            ->once()
            ->getMock();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV, 'B')
            ->once()
            ->andReturn($this->rulesForAlphaSplit);

        $this->mockSaveTask();
        $result = $this->sut->handleCommand($command);
        $expected = ['id' => ['task' => 123], 'messages' => ['Task created successfully']];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with goods licence, alpha split, unknown organisation type
     */
    public function testHandleCommandWithAutoAssignmentGoodsLicenceAlphaSplitUnknownOrgType(): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence();
        $taskAlphaSplits = new ArrayCollection();

        $this->mockLicence
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getType')
                    ->andReturn('foo')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->ruleForAlphaSplit
            ->shouldReceive('getTaskAlphaSplits')
            ->andReturn($taskAlphaSplits)
            ->once()
            ->getMock();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV, 'B')
            ->once()
            ->andReturn($this->rulesForAlphaSplit);

        $this->mockSaveTask();
        $result = $this->sut->handleCommand($command);
        $expected = ['id' => ['task' => 123], 'messages' => ['Task created successfully']];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test handle command with auto assignment with goods licence, alpha split, organisation type - sole trader
     *
     * @param string $orgType
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('orgTypeProvider')]
    public function testHandleCommandWithAutoAssignmentGoodsLicenceAlphaSplitOrgTypeSoleTraderOrPartnership(mixed $orgType): void
    {
        $command = Cmd::create($this->getData());

        $this->mockLicence();

        $mockTaskAlphaSplit = m::mock(TaskAlphaSplit::class)->makePartial();
        $mockTaskAlphaSplit->setLetters('ABOC');
        $mockTaskAlphaSplit->setUser($this->references[User::class][888]);

        $taskAlphaSplits = new ArrayCollection();
        $taskAlphaSplits->add($mockTaskAlphaSplit);

        $organisationPersons = new ArrayCollection();
        $organisationPersons->add(
            m::mock(Person::class)
            ->shouldReceive('getPerson')
            ->andReturn(
                m::mock()
                ->shouldReceive('getFamilyName')
                ->andReturn('bar')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock()
        );

        $this->mockLicence
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                ->shouldReceive('getType')
                ->andReturn($orgType)
                ->once()
                ->getMock()
                ->shouldReceive('getOrganisationPersons')
                ->andReturn($organisationPersons)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->ruleForAlphaSplit
            ->shouldReceive('getTaskAlphaSplits')
            ->andReturn($taskAlphaSplits)
            ->once()
            ->getMock();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_PSV, 'B')
            ->once()
            ->andReturn($this->rulesForAlphaSplit);

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Organisation type provider
     *
     * @return array
     */
    public static function orgTypeProvider(): array
    {
        return [
            [Organisation::ORG_TYPE_SOLE_TRADER],
            [Organisation::ORG_TYPE_PARTNERSHIP]
        ];
    }

    /**
     * Get data
     *
     * @param int $licenceId
     * @return array
     */
    protected function getData(?int $licenceId = 222): array
    {
        return [
            'category' => 1,
            'subCategory' => 2,
            'assignedToUser' => null,
            'assignedToTeam' => null,
            'application' => 111,
            'licence' => $licenceId,
            'actionDate' => '2015-01-01',
            'description' => 'Some task',
            'isClosed' => false,
            'urgent' => false
        ];
    }

    /**
     * Mock save task
     *
     * @param Mock|null $user
     */
    protected function mockSaveTask(mixed $user = null): void
    {
        $this->repoMap[Repository\Task::class]->shouldReceive('save')
            ->once()
            ->with(m::type(TaskEntity::class))
            ->andReturnUsing(
                function (TaskEntity $task) use ($user) {
                    $task->setId(123);
                    $this->assertSame($user, $task->getAssignedToUser());
                    $this->assertSame($this->references[Team::class][999], $task->getAssignedToTeam());
                }
            );
    }

    /**
     * Mock save task
     *
     * @param sting $operatorType
     * @param bool $mockOrganisation
     */
    protected function mockLicence(mixed $operatorType = Licence::LICENCE_CATEGORY_PSV, bool $mockOrganisation = false): void
    {
        $this->mockLicence
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn($operatorType)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getTrafficAreaForTaskAllocation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn('B')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        if ($mockOrganisation) {
            $this->mockLicence
                ->shouldReceive('getOrganisation')
                ->andReturn(
                    m::mock()
                        ->shouldReceive('isMlh')
                        ->andReturn(false)
                        ->once()
                        ->getMock()
                )
                ->once()
                ->getMock();
        }
    }

    /**
     * Test handle command with auto assignment with goods licence with no operator type
     */
    public function testHandleCommandWithAutoAssignmentGoodsLicenceNoOperatorType(): void
    {
        $command = Cmd::create($this->getData());

        $mockApp = m::mock()
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $apps = new ArrayCollection();
        $apps->add($mockApp);

        $this->mockLicence
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getTrafficAreaForTaskAllocation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn('B')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMlh')
                    ->andReturn(false)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getNewApplications')
            ->andReturn($apps)
            ->once()
            ->getMock();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchByParametersWithFallbackWhenSubCategoryNotFound')
            ->with(1, 2, Licence::LICENCE_CATEGORY_GOODS_VEHICLE, 'B', false)
            ->once()
            ->andReturn($this->rules);

        $this->mockSaveTask($this->references[User::class][888]);
        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => ['task' => 123, 'assignedToUser' => 888],
            'messages' => ['Task created successfully']
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
