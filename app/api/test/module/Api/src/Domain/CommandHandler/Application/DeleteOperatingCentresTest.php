<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Transfer\Command\Application\DeleteOperatingCentres as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Application\DeleteOperatingCentres::class)]
class DeleteOperatingCentresTest extends AbstractCommandHandlerTestCase
{
    /** @var CommandHandler\Application\DeleteOperatingCentres  */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CommandHandler\Application\DeleteOperatingCentres();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('ApplicationOperatingCentre', Repository\ApplicationOperatingCentre::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            OperatingCentre::class => [
                1 => m::mock(OperatingCentre::class),
            ],
            Application::class => [
                111 => m::mock(Application::class)->makePartial(),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'application' => 111,
            'ids' => [
                123
            ]
        ];
        $command = Cmd::create($data);

        /** @var ApplicationOperatingCentre $aoc1 */
        $aoc1 = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc1->setId(123);
        $aoc1->setOperatingCentre($this->mapReference(OperatingCentre::class, 1));
        $aoc1->setApplication($this->mapReference(Application::class, 111));

        /** @var ApplicationOperatingCentre $aoc2 */
        $aoc2 = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc2->setId(321);

        $aocs = new ArrayCollection();
        $aocs->add($aoc1);
        $aocs->add($aoc2);

        /** @var Application $application */
        $application = $this->mapReference(Application::class, 111);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('delete')
            ->once()
            ->with($aoc1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            [
                'id' => 111,
                'section' => 'operatingCentres'
            ],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('UPDATE_APPLICATION_COMPLETION')
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteConditionUndertakings::class,
            [
                'operatingCentre' => $this->mapReference(OperatingCentre::class, 1),
                'application' => $application,
            ],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('DELETE_CONDITIONS_UNDERTAKINGS')
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteApplicationLinks::class,
            ['operatingCentre' => $this->mapReference(OperatingCentre::class, 1)],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('DELETE_OTHER_APPLICATIONS')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'DELETE_CONDITIONS_UNDERTAKINGS',
                'DELETE_OTHER_APPLICATIONS',
                '1 Operating Centre(s) removed',
                'UPDATE_APPLICATION_COMPLETION',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandCannotDelete(): void
    {
        $data = [
            'application' => 111,
            'ids' => [
                123
            ]
        ];
        $command = Cmd::create($data);

        /** @var ApplicationOperatingCentre $aoc1 */
        $aoc1 = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc1->setId(123);
        $aoc1->shouldReceive('checkCanDelete')->with()->once()->andReturn(['ERROR' => 'Foo']);

        /** @var ApplicationOperatingCentre $aoc2 */
        $aoc2 = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc2->setId(321);

        $aocs = new ArrayCollection();
        $aocs->add($aoc1);
        $aocs->add($aoc2);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandRemovingAllOcs(): void
    {
        $data = [
            'application' => 111,
            'ids' => [
                123
            ]
        ];
        $command = Cmd::create($data);

        /** @var ApplicationOperatingCentre $aoc1 */
        $aoc1 = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc1->setId(123);
        $aoc1->setOperatingCentre($this->mapReference(OperatingCentre::class, 1));
        $aoc1->setApplication($this->mapReference(Application::class, 111));

        $aocs = new ArrayCollection();
        $aocs->add($aoc1);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('setEnforcementArea')
            ->once()
            ->with(null)
            ->shouldReceive('setTrafficArea')
            ->once()
            ->with(null);

        /** @var Application $application */
        $application = $this->mapReference(Application::class, 111);
        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('delete')
            ->once()
            ->with($aoc1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            [
                'id' => 111,
                'section' => 'operatingCentres'
            ],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('UPDATE_APPLICATION_COMPLETION')
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteConditionUndertakings::class,
            [
                'operatingCentre' => $this->mapReference(OperatingCentre::class, 1),
                'application' => $application,
            ],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('DELETE_CONDITIONS_UNDERTAKINGS')
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteApplicationLinks::class,
            ['operatingCentre' => $this->mapReference(OperatingCentre::class, 1)],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('DELETE_OTHER_APPLICATIONS')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'DELETE_CONDITIONS_UNDERTAKINGS',
                'DELETE_OTHER_APPLICATIONS',
                '1 Operating Centre(s) removed',
                'Updated traffic area',
                'Updated enforcement area',
                'UPDATE_APPLICATION_COMPLETION',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
