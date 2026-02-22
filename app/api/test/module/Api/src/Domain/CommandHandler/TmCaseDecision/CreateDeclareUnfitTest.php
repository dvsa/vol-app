<?php

declare(strict_types=1);

/**
 * Create DeclareUnfit Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmCaseDecision;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision\CreateDeclareUnfit;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\Repository\TmCaseDecision as TmCaseDecisionRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as TmCaseDecisionEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\TmCaseDecision\CreateDeclareUnfit as Cmd;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create DeclareUnfit Test
 */
class CreateDeclareUnfitTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateDeclareUnfit();
        $this->mockRepo('TmCaseDecision', TmCaseDecisionRepo::class);
        $this->mockRepo('TransportManager', TransportManagerRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)->makePartial(),
        ];

        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class)
            ->shouldReceive('getId')
            ->andReturn(2121)
            ->shouldReceive('getTeam')
            ->andReturn(
                m::mock(UserEntity\Team::class)->shouldReceive('getId')->andReturn(442)->getMock()
            )
            ->getMock();

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            TmCaseDecisionEntity::DECISION_DECLARE_UNFIT,
            'unfitnessReason',
            'rehabMeasure',
        ];

        $this->references = [
            CasesEntity::class => [
                11 => m::mock(CasesEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'case' => 11,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'unfitnessStartDate' => '2016-02-01',
            'unfitnessEndDate' => '2016-02-01',
            'unfitnessReasons' => ['unfitnessReason'],
            'rehabMeasures' => ['rehabMeasure'],
        ];

        $transportManagerId = 44;

        $transportManager = m::mock(TransportManager::class)->makePartial();
        $transportManager->setId($transportManagerId);

        $this->references[CasesEntity::class][11]->shouldReceive('getTransportManager')
            ->once()
            ->andReturn($transportManager);

        $command = Cmd::create($data);

        $this->repoMap['TmCaseDecision']->shouldReceive('save')
            ->once()
            ->with(m::type(TmCaseDecisionEntity::class))
            ->andReturnUsing(
                function (TmCaseDecisionEntity $entity) {
                    $entity->setId(111);
                }
            );

        $this->repoMap['TransportManager']->shouldReceive('save')
            ->once()
            ->with(m::type(TransportManager::class));

        $result = new Result();
        $this->expectedSideEffect(
            CreateTaskCmd::class,
            [
                'category' => Category::CATEGORY_TRANSPORT_MANAGER,
                'subCategory' => SubCategory::TM_SUB_CATEGORY_DECLARED_UNFIT,
                'description' => 'TM declared unfitness end date ' . $command->getUnfitnessEndDate(),
                'actionDate' => $command->getUnfitnessEndDate(),
                'assignedToUser' => 2121,
                'assignedToTeam' => 442,
                'case' => $data['case'],
                'transportManager' => $transportManagerId,
            ],
            $result
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'tmCaseDecision' => 111,
            ],
            'messages' => [
                'Decision created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
