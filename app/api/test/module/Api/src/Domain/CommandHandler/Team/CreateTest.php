<?php

declare(strict_types=1);

/**
 * Create Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Team;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Team\CreateTeam as CreateTeam;
use Dvsa\Olcs\Api\Domain\Repository\Team as TeamRepo;
use Dvsa\Olcs\Api\Domain\Repository\Printer as PrinterRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Team\CreateTeam as Cmd;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateTeam();
        $this->mockRepo('Team', TeamRepo::class);
        $this->mockRepo('Printer', PrinterRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            TrafficAreaEntity::class => [
                5 => m::mock(TrafficAreaEntity::class)
            ],
            PrinterEntity::class => [
                6 => m::mock(PrinterEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(
            [
                'name' => 'foo',
                'description' => 'bar',
                'trafficArea' => 5,
                'defaultPrinter' => 2
            ]
        );

        $team = null;
        $this->repoMap['Team']
            ->shouldReceive('fetchByName')
            ->with('foo')
            ->once()
            ->andReturn([])
            ->shouldReceive('save')
            ->once()
            ->with(m::type(TeamEntity::class))
            ->andReturnUsing(
                function (TeamEntity $tm) use (&$team) {
                    $tm->setId(111);
                    $team = $tm;
                }
            )
            ->getMock();

        $this->repoMap['Printer']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn(m::mock(PrinterEntity::class))
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['team']);
    }

    public function testHandleCommandWithVaidationException(): void
    {
        $this->expectException(ValidationException::class);

        $command = Cmd::create(
            [
                'name' => 'foo',
                'description' => 'bar',
                'trafficArea' => 5,
                'defaultPrinter' => 2
            ]
        );

        $this->repoMap['Team']
            ->shouldReceive('fetchByName')
            ->with('foo')
            ->once()
            ->andReturn(['foo'])
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
