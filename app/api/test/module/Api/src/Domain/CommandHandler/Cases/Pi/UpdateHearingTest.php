<?php

declare(strict_types=1);

/**
 * Update Hearing Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Pi;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi\UpdateHearing;
use Dvsa\Olcs\Api\Domain\Repository\PiHearing as PiHearingRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateHearing as Cmd;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Venue as VenueEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing as PiHearingEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\Command\Publication\PiHearing as PublishHearingCmd;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;

/**
 * Update Hearing Test
 */
class UpdateHearingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHearing();
        $this->mockRepo('PiHearing', PiHearingRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'tc_r_dhtru'
        ];

        $this->references = [
            PresidingTcEntity::class => [
                44 => m::mock(PresidingTcEntity::class)
            ],
            VenueEntity::class => [
                66 => m::mock(VenueEntity::class)
            ],
        ];

        parent::initReferences();
    }

    /**
     *
     * @param $isTm
     * @param $extraTaskKey
     * @param $venue
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $isTm, mixed $extraTaskKey, mixed $venue): void
    {
        $piId = 99;
        $hearingId = 11;
        $version = 99;
        $extraTaskId = 22;
        $case = 24;
        $presidingTc = 44;
        $userId = 77;
        $teamId = 88;
        $venueOther = 'venue other';
        $presidedByRole = 'tc_r_dhtru';
        $witnesses = 5;
        $hearingDate = '2015-12-25 12:00:00';
        $isCancelled = 'N';
        $cancelledReason = null;
        $cancelledDate = null;
        $isAdjourned = 'Y';
        $adjournedReason = null;
        $adjournedDate = '2015-12-25 18:30:00';
        $details = 'details';
        $trafficAreas = ['M'];
        $pubType = 'A&D';
        $publish = 'Y';

        $command = Cmd::Create(
            [
                'id' => $hearingId,
                'version' => $version,
                'venue' => $venue,
                'venueOther' => $venueOther,
                'presidingTc' => $presidingTc,
                'presidedByRole' => $presidedByRole,
                'witnesses' => $witnesses,
                'hearingDate' => $hearingDate,
                'isCancelled' => $isCancelled,
                'cancelledReason' => $cancelledReason,
                'cancelledDate' => $cancelledDate,
                'isAdjourned' => $isAdjourned,
                'adjournedReason' => $adjournedReason,
                'adjournedDate' => $adjournedDate,
                'pubType' => $pubType,
                'trafficAreas' => $trafficAreas,
                'details' => $details,
                'publish' => $publish
            ]
        );

        $user = m::mock(UserEntity::class);
        $user->shouldReceive('getId')->once()->andReturn($userId);
        $user->shouldReceive('getTeam->getId')->once()->andReturn($teamId);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $cases = m::mock(CasesEntity::class);
        $cases->shouldReceive('getId')->andReturn($case);
        $cases->shouldReceive('getTransportManager->getId')->andReturn($extraTaskId);
        $cases->shouldReceive('getLicence->getId')->andReturn($extraTaskId);
        $cases->shouldReceive('isTm')->andReturn($isTm);

        $pi = m::mock(PiEntity::class);
        $pi->shouldReceive('getId')->andReturn($piId);
        $pi->shouldReceive('getCase')->andReturn($cases);
        $pi->shouldReceive('isClosed')->once()->andReturn(false);
        $pi->shouldReceive('getAgreedDate')->once()->andReturn(null);

        $piHearing = m::mock(PiHearingEntity::class)->makePartial();
        $piHearing->shouldReceive('getId')->andReturn($hearingId);
        $piHearing->shouldReceive('getPi')->andReturn($pi);

        $this->repoMap['PiHearing']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($piHearing)
            ->shouldReceive('save')
            ->with(m::type(PiHearingEntity::class))
            ->once();

        $result1 = new Result();
        $publishData = [
            'id' => $hearingId,
            'pubType' => [$pubType],
            'trafficAreas' => $trafficAreas,
            'text2' => $details
        ];
        $this->expectedSideEffect(PublishHearingCmd::class, $publishData, $result1);

        $result2 = new Result();
        $actionDate = date('Y-m-d', mktime((int) date("H"), (int) date("i"), (int) date("s"), (int) date("n"), (int) date("j") + 7, (int) date("Y")));
        $taskData = [
            'category' => TaskEntity::CATEGORY_COMPLIANCE,
            'subCategory' => TaskEntity::SUB_CATEGORY_HEARINGS_APPEALS,
            'description' => 'Verify adjournment of case',
            'actionDate' => $actionDate,
            'urgent' => 'Y',
            'assignedToUser' => $userId,
            'assignedToTeam' => $teamId,
            'case' => $case,
            $extraTaskKey => $extraTaskId
        ];

        $this->expectedSideEffect(
            CreateTaskCmd::class,
            $taskData,
            $result2
        );

        $this->expectedSideEffect(
            GenerateSlaTargetDateCmd::class,
            [
                'pi' => $piId
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * @return array
     */
    public static function handleCommandProvider(): array
    {
        return [
            [true, 'transportManager', null],
            [false, 'licence', 66]
        ];
    }
}
