<?php

declare(strict_types=1);

/**
 * Create Agreed And Legislation Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Pi;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi\CreateAgreedAndLegislation;
use Dvsa\Olcs\Api\Domain\Repository\Pi as PiRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\CreateAgreedAndLegislation as Cmd;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Pi\Reason as ReasonEntity;

/**
 * Create Agreed And Legislation Test
 */
class CreateAgreedAndLegislationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateAgreedAndLegislation();
        $this->mockRepo('Pi', PiRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'tc_r_dhtru',
            PiEntity::STATUS_REGISTERED,
            'pi_t_imp',
            'pi_t_other'
        ];

        $this->references = [
            PresidingTcEntity::class => [
                44 => m::mock(PresidingTcEntity::class)
            ],
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)
            ],
            ReasonEntity::class => [
                5 => m::mock(ReasonEntity::class)
            ],
            ReasonEntity::class => [
                7 => m::mock(ReasonEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 22;
        $agreedByTc = 44;
        $agreedByTcRole = 'tc_r_dhtru';
        $agreedDate = '2015-06-12';
        $comment = 'comment';
        $piTypes = ['pi_t_imp', 'pi_t_other'];
        $reasons = [5,7];
        $case = 24;
        $assignedCaseworker = 10;
        $isEcmsCase = true;
        $ecmsFirstReceivedDate = '2015-10-11';

        $command = Cmd::Create(
            [
                'case' => $case,
                'comment' => $comment,
                'agreedByTc' => $agreedByTc,
                'agreedByTcRole' => $agreedByTcRole,
                'agreedDate' => $agreedDate,
                'piTypes' => $piTypes,
                'reasons' => $reasons,
                'assignedCaseworker' => $assignedCaseworker,
                'isEcmsCase' => $isEcmsCase,
                'ecmsFirstReceivedDate' => $ecmsFirstReceivedDate
            ]
        );

        $this->repoMap['Pi']->shouldReceive('save')
            ->with(m::type(PiEntity::class))
            ->once()
            ->andReturnUsing(
                function (PiEntity $pi) use ($id) {
                    $pi->setId($id);
                }
            );

        $this->expectedSideEffect(
            GenerateSlaTargetDateCmd::class,
            [
                'pi' => $id
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}
