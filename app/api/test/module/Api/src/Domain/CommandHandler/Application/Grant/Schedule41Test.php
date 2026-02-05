<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\Schedule41 as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Schedule41Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Schedule41Test extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant\Schedule41();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('LicenceOperatingCentre', \Dvsa\Olcs\Api\Domain\Repository\LicenceOperatingCentre::class);
        $this->mockRepo('ConditionUndertaking', \Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    public function testHandleCommandNoS4Aoc(): void
    {
        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        $organisation = new Organisation();

        $licence = new Licence($organisation, new RefData());

        $operatingCentre1 = new OperatingCentre();
        $operatingCentre2 = new OperatingCentre();

        $application = new Application($licence, new RefData(), 'N');
        $application->setId(111);

        $aoc1 = new ApplicationOperatingCentre($application, $operatingCentre1);
        $application->addOperatingCentres($aoc1);
        $aoc2 = new ApplicationOperatingCentre($application, $operatingCentre2);
        $application->addOperatingCentres($aoc2);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '0 S4 operating centres processed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNoS4Surrender(): void
    {
        $data = [
            'id' => 111
        ];

        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);
        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);
        $mockUser->setTeam($mockTeam);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        $organisation = new Organisation();

        $licence = new Licence($organisation, new RefData());
        $licence->setLicNo('LIC01');
        $licence->setId(74);

        $operatingCentre1 = new OperatingCentre();
        $operatingCentre2 = new OperatingCentre();

        $application = new Application($licence, new RefData(), 'N');
        $application->setId(111);

        $s4 = new S4($application, $licence);
        $s4->setSurrenderLicence('N');

        $aoc1 = new ApplicationOperatingCentre($application, $operatingCentre1);
        $aoc1->setS4($s4);
        $application->addOperatingCentres($aoc1);
        $aoc2 = new ApplicationOperatingCentre($application, $operatingCentre2);
        $aoc2->setS4($s4);
        $application->addOperatingCentres($aoc2);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '2 S4 operating centres processed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandDeleteAll(): void
    {
        $data = [
            'id' => 111
        ];

        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);
        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);
        $mockUser->setTeam($mockTeam);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        $organisation = new Organisation();

        $licence = new Licence($organisation, new RefData());
        $licence->setLicNo('LIC01');
        $licence->setId(74);

        $operatingCentre1 = new OperatingCentre();
        $operatingCentre2 = new OperatingCentre();

        $application = new Application($licence, new RefData(), 'N');
        $application->setId(111);

        $s4 = new S4($application, $licence);
        $s4->setSurrenderLicence('Y');

        $aoc1 = new ApplicationOperatingCentre($application, $operatingCentre1);
        $aoc1->setS4($s4);
        $application->addOperatingCentres($aoc1);
        $aoc2 = new ApplicationOperatingCentre($application, $operatingCentre2);
        $aoc2->setS4($s4);
        $application->addOperatingCentres($aoc2);

        $loc1 = new LicenceOperatingCentre($licence, $operatingCentre1);
        $licence->addOperatingCentres($loc1);
        $loc2 = new LicenceOperatingCentre($licence, $operatingCentre2);
        $licence->addOperatingCentres($loc2);

        $cu1 = new ConditionUndertaking(new RefData(), 'Y', 'N');
        $cu1->setOperatingCentre($operatingCentre1);
        $licence->addConditionUndertakings($cu1);
        $cu2 = new ConditionUndertaking(new RefData(), 'Y', 'N');
        $cu2->setOperatingCentre($operatingCentre2);
        $licence->addConditionUndertakings($cu2);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('delete')->with($loc1)->once();
        $this->repoMap['LicenceOperatingCentre']->shouldReceive('delete')->with($loc2)->once();

        $this->repoMap['ConditionUndertaking']->shouldReceive('delete')->with($cu1)->once();
        $this->repoMap['ConditionUndertaking']->shouldReceive('delete')->with($cu2)->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::class,
            [
                'category' => Category::CATEGORY_LICENSING,
                'subCategory' => Category::TASK_SUB_CATEGORY_SUR_41_ASSISTED_DIGITAL,
                'description' => 'Surrender a donor licence: LIC01',
                'actionDate' => (new DateTime())->modify('+1 month')->format(\DateTime::W3C),
                'licence' => 74,
                'assignedToUser' => 1,
                'assignedToTeam' => 2,
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '2 S4 operating centres processed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandDeleteSome(): void
    {
        $data = [
            'id' => 111
        ];

        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);
        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);
        $mockUser->setTeam($mockTeam);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        $organisation = new Organisation();

        $licence = new Licence($organisation, new RefData());
        $licence->setLicNo('LIC01');
        $licence->setId(74);

        $operatingCentre1 = new OperatingCentre();
        $operatingCentre2 = new OperatingCentre();

        $application = new Application($licence, new RefData(), 'N');
        $application->setId(111);

        $s4 = new S4($application, $licence);
        $s4->setSurrenderLicence('Y');

        $aoc1 = new ApplicationOperatingCentre($application, $operatingCentre1);
        $aoc1->setS4($s4);
        $application->addOperatingCentres($aoc1);
        $aoc2 = new ApplicationOperatingCentre($application, $operatingCentre2);
        $aoc2->setS4($s4);
        $application->addOperatingCentres($aoc2);

        $loc2 = new LicenceOperatingCentre($licence, $operatingCentre2);
        $licence->addOperatingCentres($loc2);

        $cu1 = new ConditionUndertaking(new RefData(), 'Y', 'N');
        $licence->addConditionUndertakings($cu1);
        $cu2 = new ConditionUndertaking(new RefData(), 'Y', 'N');
        $cu2->setOperatingCentre($operatingCentre2);
        $licence->addConditionUndertakings($cu2);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('delete')->with($loc2)->once();

        $this->repoMap['ConditionUndertaking']->shouldReceive('delete')->with($cu2)->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::class,
            [
                'category' => Category::CATEGORY_LICENSING,
                'subCategory' => Category::TASK_SUB_CATEGORY_SUR_41_ASSISTED_DIGITAL,
                'description' => 'Surrender a donor licence: LIC01',
                'actionDate' => (new DateTime())->modify('+1 month')->format(\DateTime::W3C),
                'licence' => 74,
                'assignedToUser' => 1,
                'assignedToTeam' => 2,
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '2 S4 operating centres processed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testIssueOcls16990(): void
    {
        $data = [
            'id' => 111
        ];

        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);
        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);
        $mockUser->setTeam($mockTeam);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        $organisation = new Organisation();

        $licence = new Licence($organisation, new RefData());
        $licence->setLicNo('LIC01');
        $licence->setId(74);

        $operatingCentre1 = new OperatingCentre();
        $operatingCentre2 = new OperatingCentre();

        $application = new Application($licence, new RefData(), 'N');
        $application->setId(111);

        $s4 = new S4($application, $licence);
        $s4->setSurrenderLicence('Y');

        $aoc1 = new ApplicationOperatingCentre($application, $operatingCentre1);
        $aoc1->setS4($s4);
        $application->addOperatingCentres($aoc1);
        $aoc2 = new ApplicationOperatingCentre($application, $operatingCentre2);
        $application->addOperatingCentres($aoc2);

        $loc2 = new LicenceOperatingCentre($licence, $operatingCentre1);
        $licence->addOperatingCentres($loc2);

        $cu1 = new ConditionUndertaking(new RefData(), 'Y', 'N');
        $licence->addConditionUndertakings($cu1);
        $cu2 = new ConditionUndertaking(new RefData(), 'Y', 'N');
        $cu2->setOperatingCentre($operatingCentre1);
        $licence->addConditionUndertakings($cu2);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('delete')->with($loc2)->once();

        $this->repoMap['ConditionUndertaking']->shouldReceive('delete')->with($cu2)->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::class,
            [
                'category' => Category::CATEGORY_LICENSING,
                'subCategory' => Category::TASK_SUB_CATEGORY_SUR_41_ASSISTED_DIGITAL,
                'description' => 'Surrender a donor licence: LIC01',
                'actionDate' => (new DateTime())->modify('+1 month')->format(\DateTime::W3C),
                'licence' => 74,
                'assignedToUser' => 1,
                'assignedToTeam' => 2,
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 S4 operating centres processed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
