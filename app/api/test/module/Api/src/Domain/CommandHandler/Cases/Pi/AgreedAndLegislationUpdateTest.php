<?php

declare(strict_types=1);

/**
 * Agreed and Legislation Update Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Pi;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi\AgreedAndLegislationUpdate;
use Dvsa\Olcs\Api\Domain\Repository\Pi as PiRepo;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateAgreedAndLegislation as Cmd;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Pi\Reason as ReasonEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;

/**
 * Agreed and Legislation Update Test
 */
class AgreedAndLegislationUpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AgreedAndLegislationUpdate();
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
            ],
            UserEntity::class => [
                20 => m::mock(UserEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $agreedByTc = 44;
        $agreedByTcRole = 'tc_r_dhtru';
        $agreedDate = '2015-06-12';
        $comment = 'comment';
        $piTypes = ['pi_t_imp', 'pi_t_other'];
        $reasons = [5,7];
        $id = 22;
        $version = 33;

        $command = Cmd::Create(
            [
                'id' => $id,
                'version' => $version,
                'comment' => $comment,
                'agreedByTc' => $agreedByTc,
                'agreedByTcRole' => $agreedByTcRole,
                'agreedDate' => $agreedDate,
                'piTypes' => $piTypes,
                'reasons' => $reasons,
            ]
        );

        $pi = m::mock(PiEntity::class)->makePartial();

        $this->repoMap['Pi']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($pi)
            ->shouldReceive('save')
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

    public function testHandleCommandEcmsCaseFalseNoAssignedCaseworkerNoEcmsDate(): void
    {
        $agreedByTc = 44;
        $agreedByTcRole = 'tc_r_dhtru';
        $agreedDate = '2015-06-12';
        $comment = 'comment';
        $piTypes = ['pi_t_imp', 'pi_t_other'];
        $reasons = [5,7];
        $id = 22;
        $version = 33;
        $isEcmsCase = 'N';
        $assignedCaseworker = null;
        $ecmsFirstReceivedDate = null;
        $commandData = [
            'id' => $id,
            'version' => $version,
            'comment' => $comment,
            'agreedByTc' => $agreedByTc,
            'agreedByTcRole' => $agreedByTcRole,
            'agreedDate' => $agreedDate,
            'piTypes' => $piTypes,
            'reasons' => $reasons,
            'isEcmsCase' => $isEcmsCase,
            'assignedCaseworker' => $assignedCaseworker,
            'ecmsFirstReceivedDate' => $ecmsFirstReceivedDate
        ];

        $command = Cmd::Create($commandData);

        /**@var PiEntity $pi **/
        $pi = m::mock(PiEntity::class)->makePartial();

        $this->repoMap['Pi']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($pi)
            ->shouldReceive('save')
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
        $this->assertEquals(0, $pi->getIsEcmsCase());
        $this->assertNull($pi->getAssignedCaseworker());
        $this->assertNull($pi->getEcmsFirstReceivedDate());
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleCommandEcmsCaseTrueNoAssignedCaseworkerNoEcmsDate(): void
    {
        $agreedByTc = 44;
        $agreedByTcRole = 'tc_r_dhtru';
        $agreedDate = '2015-06-12';
        $comment = 'comment';
        $piTypes = ['pi_t_imp', 'pi_t_other'];
        $reasons = [5,7];
        $id = 22;
        $version = 33;
        $isEcmsCase = 'Y';
        $assignedCaseworker = null;
        $ecmsFirstReceivedDate = null;
        $commandData = [
            'id' => $id,
            'version' => $version,
            'comment' => $comment,
            'agreedByTc' => $agreedByTc,
            'agreedByTcRole' => $agreedByTcRole,
            'agreedDate' => $agreedDate,
            'piTypes' => $piTypes,
            'reasons' => $reasons,
            'isEcmsCase' => $isEcmsCase,
            'assignedCaseworker' => $assignedCaseworker,
            'ecmsFirstReceivedDate' => $ecmsFirstReceivedDate
        ];

        $command = Cmd::Create($commandData);

        /**@var PiEntity $pi **/
        $pi = m::mock(PiEntity::class)->makePartial();

        $this->repoMap['Pi']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($pi)
            ->shouldReceive('save')
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
        $this->assertEquals(1, $pi->getIsEcmsCase());
        $this->assertNull($pi->getAssignedCaseworker());
        $this->assertNull($pi->getEcmsFirstReceivedDate());
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleCommandEcmsCaseTrueWithAssignedCaseworkerWithDate(): void
    {
        $agreedByTc = 44;
        $agreedByTcRole = 'tc_r_dhtru';
        $agreedDate = '2015-06-12';
        $comment = 'comment';
        $piTypes = ['pi_t_imp', 'pi_t_other'];
        $reasons = [5,7];
        $id = 22;
        $version = 33;
        $isEcmsCase = 'Y';
        $assignedCaseworker = 20;
        $ecmsFirstReceivedDate = '2015-12-12';
        $commandData = [
            'id' => $id,
            'version' => $version,
            'comment' => $comment,
            'agreedByTc' => $agreedByTc,
            'agreedByTcRole' => $agreedByTcRole,
            'agreedDate' => $agreedDate,
            'piTypes' => $piTypes,
            'reasons' => $reasons,
            'isEcmsCase' => $isEcmsCase,
            'assignedCaseworker' => $assignedCaseworker,
            'ecmsFirstReceivedDate' => $ecmsFirstReceivedDate
        ];

        $command = Cmd::Create($commandData);

        /**@var PiEntity $pi **/
        $pi = m::mock(PiEntity::class)->makePartial();

        $this->repoMap['Pi']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($pi)
            ->shouldReceive('save')
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
        $this->assertEquals(1, $pi->getIsEcmsCase());
        $this->assertEquals(
            (new DateTime('2015-12-12', new \DateTimeZone('UTC')))->format('Y-m-d'),
            $pi->getEcmsFirstReceivedDate()->format('Y-m-d')
        );
        $this->assertEquals($assignedCaseworker, $pi->getAssignedCaseworker()->getId());
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleCommandEcmsCaseFalseWithAssignedCaseworkerWithDate(): void
    {
        $agreedByTc = 44;
        $agreedByTcRole = 'tc_r_dhtru';
        $agreedDate = '2015-06-12';
        $comment = 'comment';
        $piTypes = ['pi_t_imp', 'pi_t_other'];
        $reasons = [5,7];
        $id = 22;
        $version = 33;
        $isEcmsCase = 'N';
        $assignedCaseworker = 20;
        $ecmsFirstReceivedDate = '2015-12-12';
        $commandData = [
            'id' => $id,
            'version' => $version,
            'comment' => $comment,
            'agreedByTc' => $agreedByTc,
            'agreedByTcRole' => $agreedByTcRole,
            'agreedDate' => $agreedDate,
            'piTypes' => $piTypes,
            'reasons' => $reasons,
            'isEcmsCase' => $isEcmsCase,
            'assignedCaseworker' => $assignedCaseworker,
            'ecmsFirstReceivedDate' => $ecmsFirstReceivedDate
        ];

        $command = Cmd::Create($commandData);

        /**@var PiEntity $pi **/
        $pi = m::mock(PiEntity::class)->makePartial();

        $this->repoMap['Pi']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($pi)
            ->shouldReceive('save')
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
        $this->assertEquals(0, $pi->getIsEcmsCase());
        $this->assertNull($pi->getEcmsFirstReceivedDate());
        $this->assertEquals($assignedCaseworker, $pi->getAssignedCaseworker()->getId());
        $this->assertInstanceOf(Result::class, $result);
    }
}
