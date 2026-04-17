<?php

declare(strict_types=1);

/**
 * Assign Submission Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Query\User\User;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Submission\AssignSubmission;
use Dvsa\Olcs\Api\Domain\Repository\Submission as SubmissionRepo;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Transfer\Command\Submission\AssignSubmission as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Service\Submission\SubmissionGenerator;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\TransactionManagerInterface;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;

/**
 * Assign Submission Test
 */
class AssignSubmissionTest extends AbstractCommandHandlerTestCase
{
    public $submissionConfig = [
        'submissions' => [
            'sections' => [
                'configuration' => [
                    'introduction' => [
                        'subcategoryId' => 115,
                        'config' => [],
                        'section_type' => ['text'],
                        'allow_comments' => true,
                        'allow_attachments' => true
                    ]
                ]
            ]
        ]
    ];

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new AssignSubmission();
        $this->mockRepo('Submission', SubmissionRepo::class);
        $this->mockRepo('User', UserRepo::class);
        $this->mockRepo('Task', TaskRepo::class);

        $this->mockedSmServices = [
            SubmissionGenerator::class => m::mock(SubmissionGenerator::class),
            AuthorizationService::class => m::mock(AuthorizationService::class),
            IdentityProviderInterface::class => m::mock(IdentityProviderInterface::class)
        ];

        // copied from parent,
        $this->repoManager = m::mock(RepositoryServiceManager::class);
        $this->queryHandler = m::mock(QueryHandlerManager::class);
        $this->commandHandler = m::mock(CommandHandlerManager::class);

        foreach ($this->repoMap as $alias => $service) {
            $this->repoManager
                ->shouldReceive('get')
                ->with($alias)
                ->andReturn($service);
        }

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')->with('RepositoryServiceManager')->andReturn($this->repoManager);
        $sm->shouldReceive('get')->with('TransactionManager')->andReturn(m::mock(TransactionManagerInterface::class));
        $sm->expects('get')->with('CommandHandlerManager')->andReturn($this->commandHandler);
        $sm->shouldReceive('get')->with('QueryHandlerManager')->andReturn($this->queryHandler);
        $sm->shouldReceive('get')->with('config')->andReturn($this->submissionConfig);

        foreach ($this->mockedSmServices as $serviceName => $service) {
            $sm->shouldReceive('get')->with($serviceName)->andReturn($service);
        }

        $this->sut->__invoke($sm, AssignSubmission::class);

        $this->sideEffects = [];
        $this->commands = [];

        $this->initReferences();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'sub_st_rec_grant_as'
        ];

        $this->references = [
            User::class => [
                1 => m::mock(UserEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandTeamExistsCreateTask(): void
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'recipientUser' => 4,
            'tcOrOther' => 'other',
            'urgent' => 'Y'
        ];

        /** @var User $mockUser */
        $mockUser = m::mock(UserEntity::class)->makePartial();
        $mockUser->setId(1);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        /** @var SubmissionEntity $savedSubmission */
        $submission = m::mock(SubmissionEntity::class)->makePartial();
        $submission->setId(1);

        $submission->shouldReceive('getCase->getId')->andReturn(12)->getMock();
        $submission->shouldReceive('getCase->getLicence->getId')->andReturn(121)->getMock();
        $submission->shouldReceive('getCase->getLicence->getLicNo')->andReturn('AB123456')->getMock();
        $submission->shouldReceive('getInformationCompleteDate')->andReturn('2015-01-01')->getMock();
        $submission->shouldReceive('setAssignedDate')->once()->getMock();
        $submission->shouldReceive('getCase->isTm')->andReturn(false)->getMock();

        $this->repoMap['Task']->shouldReceive('fetchAssignedToSubmission')
            ->once()
            ->with($submission)
            ->andReturnNull();

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($submission)
            ->shouldReceive('fetchWithCaseAndLicenceById')
            ->with(1)
            ->andReturn($submission)
            ->once();

        /** @var SubmissionEntity $savedSubmission */
        $savedSubmission = null;

        $this->repoMap['Submission']->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionEntity::class))
            ->andReturnUsing(
                function (SubmissionEntity $submission) use (&$savedSubmission) {
                    $savedSubmission = $submission;
                }
            );
        $team = new TeamEntity();
        $team->setId(5);
        $mockRecipientUser = m::mock()
            ->shouldReceive('getId')
            ->andReturn(4)
            ->once()
            ->shouldReceive('getTeam')
            ->andReturn(
                $team
            )
            ->getMock();

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with(4)
            ->andReturn($mockRecipientUser);

        $createTaskResult = new Result();
        $createTaskResult->addId('task', 1);
        $createTaskResult->addMessage('Task created successfully');

        $params = [
            'category' => TaskEntity::CATEGORY_SUBMISSION,
            'subCategory' => TaskEntity::SUBCATEGORY_SUBMISSION_ASSIGNMENT,
            'description' => 'Licence AB123456 Case 12 Submission 1',
            'actionDate' => date('Y-m-d'),
            'assignedToUser' => 4,
            'assignedToTeam' => 5,
            'assignedByUser' => 1,
            'case' => 12,
            'submission' => 1,
            'licence' => 121,
            'urgent' => 'Y',
            'isClosed' => 0,
            'application' => null,
            'busReg' => null,
            'transportManager' => null,
            'irfoOrganisation' => null
        ];
        $this->expectedSideEffect(CreateTaskCmd::class, $params, $createTaskResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submission' => 1,
                'task' => 1
            ],
            'messages' => [
                'Submission updated successfully',
                'Task created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandTeamDoesntExistCreateTask(): void
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'recipientUser' => 4,
            'tcOrOther' => 'other',
            'urgent' => 'Y'
        ];

        /** @var User $mockUser */
        $mockUser = m::mock(UserEntity::class)->makePartial();
        $mockUser->setId(1);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        /** @var SubmissionEntity $savedSubmission */
        $submission = m::mock(SubmissionEntity::class)->makePartial();
        $submission->setId(1);

        $submission->shouldReceive('getCase->getId')->andReturn(12)->getMock();
        $submission->shouldReceive('getCase->getLicence->getId')->andReturn(121)->getMock();
        $submission->shouldReceive('getCase->getLicence->getLicNo')->andReturn('AB123456')->getMock();
        $submission->shouldReceive('getInformationCompleteDate')->andReturn('2015-01-01')->getMock();
        $submission->shouldReceive('getCase->isTm')->andReturn(false)->getMock();

        $this->repoMap['Task']->shouldReceive('fetchAssignedToSubmission')
            ->once()
            ->with($submission)
            ->andReturnNull();

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($submission)
            ->shouldReceive('fetchWithCaseAndLicenceById')
            ->with(1)
            ->andReturn($submission)
            ->once();

        /** @var SubmissionEntity $savedSubmission */
        $savedSubmission = null;

        $this->repoMap['Submission']->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionEntity::class))
            ->andReturnUsing(
                function (SubmissionEntity $submission) use (&$savedSubmission) {
                    $savedSubmission = $submission;
                }
            );

        $mockRecipientUser = m::mock()
            ->shouldReceive('getId')
            ->andReturn(4)
            ->once()
            ->shouldReceive('getTeam')
            ->andReturn(
                null
            )
            ->getMock();

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with(4)
            ->andReturn($mockRecipientUser);

        $createTaskResult = new Result();
        $createTaskResult->addId('task', 1);
        $createTaskResult->addMessage('Task created successfully');

        $params = [
            'category' => TaskEntity::CATEGORY_SUBMISSION,
            'subCategory' => TaskEntity::SUBCATEGORY_SUBMISSION_ASSIGNMENT,
            'description' => 'Licence AB123456 Case 12 Submission 1',
            'actionDate' => date('Y-m-d'),
            'assignedToUser' => 4,
            'assignedToTeam' => null,
            'assignedByUser' => 1,
            'case' => 12,
            'submission' => 1,
            'licence' => 121,
            'urgent' => 'Y',
            'isClosed' => 0,
            'application' => null,
            'busReg' => null,
            'transportManager' => null,
            'irfoOrganisation' => null
        ];
        $this->expectedSideEffect(CreateTaskCmd::class, $params, $createTaskResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submission' => 1,
                'task' => 1
            ],
            'messages' => [
                'Submission updated successfully',
                'Task created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandInformationIncomplete(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $data = [
            'id' => 1,
            'version' => 1,
            'recipientUser' => 4,
            'tcOrOther' => 'other',
            'urgent' => 'Y'
        ];

        $command = Cmd::create($data);

        /** @var SubmissionEntity $savedSubmission */
        $submission = m::mock(SubmissionEntity::class)->makePartial();
        $submission->setId(1);

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($submission);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandTeamExistsUpdateTask(): void
    {
        $submissionId = 99;
        $licenceId = 77;
        $licNo = 'AB1234678';
        $caseId = 322;
        $data = [
            'id' => $submissionId,
            'version' => 1,
            'recipientUser' => 4,
            'tcOrOther' => 'other',
            'urgent' => 'Y'
        ];

        /** @var User $mockUser */
        $mockUser = m::mock(UserEntity::class)->makePartial();
        $mockUser->setId(1);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        /** @var SubmissionEntity $savedSubmission */
        $submission = m::mock(SubmissionEntity::class)->makePartial();
        $submission->setId($submissionId);
        $submission->shouldReceive('getInformationCompleteDate')
            ->andReturn('2015-01-01')
            ->shouldReceive('getCase')
            ->andReturn(
                m::mock(CasesEntity::class)->makePartial()
                    ->setId($caseId)
                    ->setLicence(
                        m::mock(LicenceEntity::class)->makePartial()
                            ->setId($licenceId)
                            ->setLicNo($licNo)
                    )
            );

        $task = $this->getMockTask();

        $this->repoMap['Task']->shouldReceive('fetchAssignedToSubmission')
            ->once()
            ->with($submission)
            ->andReturn($task)
            ->shouldReceive('save')
            ->once()
            ->with($task);

        /** @var SubmissionEntity $savedSubmission */
        $savedSubmission = null;

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($submission)
            ->shouldReceive('fetchWithCaseAndLicenceById')
            ->with($submissionId)
            ->andReturn($submission)
            ->once()
            ->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionEntity::class))
            ->andReturnUsing(
                function (SubmissionEntity $submission) use (&$savedSubmission) {
                    $savedSubmission = $submission;
                }
            );
        $team = new TeamEntity();
        $team->setId(5);
        $mockRecipientUser = m::mock()
            ->shouldReceive('getId')
            ->andReturn(4)
            ->shouldReceive('getTeam')
            ->andReturn(
                $team
            )
            ->getMock();

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with(4)
            ->andReturn($mockRecipientUser);

        $createTaskResult = new Result();
        $createTaskResult->addId('task', 1);
        $createTaskResult->addMessage('Task created successfully');

        $params = [
            'category' => TaskEntity::CATEGORY_SUBMISSION,
            'subCategory' => TaskEntity::SUBCATEGORY_SUBMISSION_ASSIGNMENT,
            'description' => 'Licence ' . $licNo . ' Case ' . $caseId . ' Submission ' . $submissionId,
            'assignedToUser' => 4,
            'assignedToTeam' => 5,
            'assignedByUser' => 1,
            'case' => $caseId,
            'submission' => $submissionId,
            'licence' => $licenceId,
            'urgent' => 'Y',
            'application' => null,
            'busReg' => null,
            'transportManager' => null,
            'irfoOrganisation' => null
        ];

        $this->expectedSideEffect(CreateTaskCmd::class, $params, $createTaskResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submission' => $submissionId,
                'task' => 1
            ],
            'messages' => [
                'Submission updated successfully',
                'Task created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    private function getMockTask(): mixed
    {
        /** @var TaskEntity $task */
        $task = m::mock(TaskEntity::class)->makePartial();
        $task->setId(1);
        $task->setVersion(4);

        $mockCategory = m::mock(CategoryEntity::class)->makePartial();
        $mockCategory->setId(TaskEntity::CATEGORY_SUBMISSION);

        $mockSubCategory = m::mock(SubCategoryEntity::class)->makePartial();
        $mockSubCategory->setId(TaskEntity::SUBCATEGORY_SUBMISSION_ASSIGNMENT);

        $task->setCategory($mockCategory);
        $task->setSubCategory($mockSubCategory);
        $task->setActionDate('2015-04-03');

        return $task;
    }

    public function testHandleCommandTransportManagerCase(): void
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'recipientUser' => 4,
            'tcOrOther' => 'other',
            'urgent' => 'Y'
        ];

        /** @var User $mockUser */
        $mockUser = m::mock(UserEntity::class)->makePartial();
        $mockUser->setId(1);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        /** @var SubmissionEntity $savedSubmission */
        $submission = m::mock(SubmissionEntity::class)->makePartial();
        $submission->setId(1);

        $submission->shouldReceive('getCase->getId')->andReturn(12)->getMock();
        $submission->shouldReceive('getCase->getTransportManager->getId')->andReturn(577)->getMock();
        $submission->shouldReceive('getInformationCompleteDate')->andReturn('2015-01-01')->getMock();
        $submission->shouldReceive('getCase->isTm')->andReturn(true)->getMock();

        $this->repoMap['Task']->shouldReceive('fetchAssignedToSubmission')
            ->once()
            ->with($submission)
            ->andReturnNull();

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($submission)
            ->shouldReceive('fetchWithCaseAndLicenceById')
            ->with(1)
            ->andReturn($submission)
            ->once();

        /** @var SubmissionEntity $savedSubmission */
        $savedSubmission = null;

        $this->repoMap['Submission']->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionEntity::class))
            ->andReturnUsing(
                function (SubmissionEntity $submission) use (&$savedSubmission) {
                    $savedSubmission = $submission;
                }
            );
        $team = new TeamEntity();
        $team->setId(5);
        $mockRecipientUser = m::mock()
            ->shouldReceive('getId')
            ->andReturn(4)
            ->once()
            ->shouldReceive('getTeam')
            ->andReturn(
                $team
            )
            ->getMock();

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with(4)
            ->andReturn($mockRecipientUser);

        $createTaskResult = new Result();
        $createTaskResult->addId('task', 1);
        $createTaskResult->addMessage('Task created successfully');

        $params = [
            'category' => TaskEntity::CATEGORY_SUBMISSION,
            'subCategory' => TaskEntity::SUBCATEGORY_SUBMISSION_ASSIGNMENT,
            'description' => 'Transport Manager 577 Case 12 Submission 1',
            'actionDate' => date('Y-m-d'),
            'assignedToUser' => 4,
            'assignedToTeam' => 5,
            'assignedByUser' => 1,
            'case' => 12,
            'submission' => 1,
            'urgent' => 'Y',
            'isClosed' => 0,
            'application' => null,
            'busReg' => null,
            'transportManager' => 577,
            'irfoOrganisation' => null
        ];
        $this->expectedSideEffect(CreateTaskCmd::class, $params, $createTaskResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submission' => 1,
                'task' => 1
            ],
            'messages' => [
                'Submission updated successfully',
                'Task created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function informationCompleteDateDataProvider(): array
    {
        return [
            ['2017-01-31'],
            ['2018-01-01'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('informationCompleteDateDataProvider')]
    public function testSavesWhenAssignedDateEqualToOrAfter(mixed $informationCompleteDate): void
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'recipientUser' => 4,
            'tcOrOther' => 'other',
            'urgent' => 'Y'
        ];

        $command = Cmd::create($data);

        $mockUser = m::mock(UserEntity::class)->makePartial();
        $mockUser->setId(1);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        /** @var SubmissionEntity $savedSubmission */
        $submission = m::mock(SubmissionEntity::class)->makePartial();
        $submission->setId(1);

        $submission->shouldReceive('getCase->getId')->andReturn(12)->getMock();
        $submission->shouldReceive('getCase->getTransportManager->getId')->andReturn(577)->getMock();
        $submission->shouldReceive('getInformationCompleteDate')->andReturn($informationCompleteDate)->getMock();
        $submission->shouldReceive('getCase->isTm')->andReturn(true)->getMock();

        $this->repoMap['Task']->shouldReceive('fetchAssignedToSubmission')
            ->once()
            ->with($submission)
            ->andReturnNull();

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($submission)
            ->shouldReceive('fetchWithCaseAndLicenceById')
            ->with(1)
            ->andReturn($submission)
            ->once();

        /** @var SubmissionEntity $savedSubmission */
        $savedSubmission = null;

        $this->repoMap['Submission']->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionEntity::class))
            ->andReturnUsing(
                function (SubmissionEntity $submission) use (&$savedSubmission) {
                    $savedSubmission = $submission;
                }
            );
        $team = new TeamEntity();
        $team->setId(5);
        $mockRecipientUser = m::mock()
            ->shouldReceive('getId')
            ->andReturn(4)
            ->once()
            ->shouldReceive('getTeam')
            ->andReturn(
                $team
            )
            ->getMock();

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with(4)
            ->andReturn($mockRecipientUser);

        $createTaskResult = new Result();
        $createTaskResult->addId('task', 1);
        $createTaskResult->addMessage('Task created successfully');

        $params = [
            'category' => TaskEntity::CATEGORY_SUBMISSION,
            'subCategory' => TaskEntity::SUBCATEGORY_SUBMISSION_ASSIGNMENT,
            'description' => 'Transport Manager 577 Case 12 Submission 1',
            'actionDate' => date('Y-m-d'),
            'assignedToUser' => 4,
            'assignedToTeam' => 5,
            'assignedByUser' => 1,
            'case' => 12,
            'submission' => 1,
            'urgent' => 'Y',
            'isClosed' => 0,
            'application' => null,
            'busReg' => null,
            'transportManager' => 577,
            'irfoOrganisation' => null
        ];
        $this->expectedSideEffect(CreateTaskCmd::class, $params, $createTaskResult);

        $expected = [
            'id' => [
                'submission' => 1,
                'task' => 1
            ],
            'messages' => [
                'Submission updated successfully',
                'Task created successfully'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('assignToTcDataProvider')]
    public function testHandleCommandAssignToTc(mixed $tcSlaStarted, mixed $assignDateTimes): void
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'presidingTcUser' => 6,
            'tcOrOther' => 'tc',
            'urgent' => 'Y',
        ];

        /** @var User $mockUser */
        $mockUser = m::mock(UserEntity::class)->makePartial();
        $mockUser->setId(1);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        /** @var SubmissionEntity $savedSubmission */
        $submission = m::mock(SubmissionEntity::class)->makePartial();
        $submission->setId(1);

        $submission->shouldReceive('getCase->getId')->andReturn(12)->getMock();
        $submission->shouldReceive('getCase->getLicence->getId')->andReturn(121)->getMock();
        $submission->shouldReceive('getCase->getLicence->getLicNo')->andReturn('AB123456')->getMock();
        $submission->shouldReceive('getTcSlaStarted')->andReturn($tcSlaStarted)->getMock();
        $submission->shouldReceive('setTcSlaStarted')->with(1)->getMock();
        $submission->shouldReceive('getInformationCompleteDate')->andReturn('2015-01-01')->getMock();
        $submission->shouldReceive('setAssignedDate')->times($assignDateTimes)->getMock();
        $submission->shouldReceive('getCase->isTm')->andReturn(false)->getMock();

        $this->repoMap['Task']->shouldReceive('fetchAssignedToSubmission')
            ->once()
            ->with($submission)
            ->andReturnNull();

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($submission)
            ->shouldReceive('fetchWithCaseAndLicenceById')
            ->with(1)
            ->andReturn($submission)
            ->once();

        /** @var SubmissionEntity $savedSubmission */
        $savedSubmission = null;

        $this->repoMap['Submission']->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionEntity::class))
            ->andReturnUsing(
                function (SubmissionEntity $submission) use (&$savedSubmission) {
                    $savedSubmission = $submission;
                }
            );
        $team = new TeamEntity();
        $team->setId(5);
        $mockRecipientUser = m::mock()
            ->shouldReceive('getId')
            ->andReturn(6)
            ->once()
            ->shouldReceive('getTeam')
            ->andReturn(
                $team
            )
            ->getMock();

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with(6)
            ->andReturn($mockRecipientUser);

        $createTaskResult = new Result();
        $createTaskResult->addId('task', 1);
        $createTaskResult->addMessage('Task created successfully');

        $params = [
            'category' => TaskEntity::CATEGORY_SUBMISSION,
            'subCategory' => TaskEntity::SUBCATEGORY_SUBMISSION_ASSIGNMENT,
            'description' => 'Licence AB123456 Case 12 Submission 1',
            'actionDate' => date('Y-m-d'),
            'assignedToUser' => 6,
            'assignedToTeam' => 5,
            'assignedByUser' => 1,
            'case' => 12,
            'submission' => 1,
            'licence' => 121,
            'urgent' => 'Y',
            'isClosed' => 0,
            'application' => null,
            'busReg' => null,
            'transportManager' => null,
            'irfoOrganisation' => null
        ];
        $this->expectedSideEffect(CreateTaskCmd::class, $params, $createTaskResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submission' => 1,
                'task' => 1
            ],
            'messages' => [
                'Submission updated successfully',
                'Task created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function assignToTcDataProvider(): array
    {
        return [
            [0, 1],
            [1, 0],
        ];
    }
}
