<?php

declare(strict_types=1);

/**
 * Update Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Team;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Team\UpdateTeam as UpdateTeam;
use Dvsa\Olcs\Api\Domain\Repository\Team as TeamRepo;
use Dvsa\Olcs\Api\Domain\Repository\Printer as PrinterRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Team\UpdateTeam as Cmd;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Update Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateTeam();
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
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(
            [
                'id' => 1,
                'version' => 2,
                'name' => 'foo',
                'description' => 'bar',
                'trafficArea' => 5,
                'defaultPrinter' => 3
            ]
        );

        $mockDefaultPrinter = m::mock()
            ->shouldReceive('getPrinter')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(99)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockTeam = m::mock(TeamEntity::class)
            ->shouldReceive('getId')
            ->andReturn(1)
            ->shouldReceive('setName')
            ->with('foo')
            ->once()
            ->shouldReceive('setDescription')
            ->with('bar')
            ->once()
            ->shouldReceive('setTrafficArea')
            ->with($this->references[TrafficAreaEntity::class][5])
            ->shouldReceive('getDefaultTeamPrinter')
            ->andReturn($mockDefaultPrinter)
            ->once()
            ->shouldReceive('updateDefaultPrinter')
            ->with('bar')
            ->once()
            ->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchByName')
            ->with('foo')
            ->once()
            ->andReturn([$mockTeam])
            ->shouldReceive('fetchWithPrinters')
            ->with(1, \Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->andReturn($mockTeam)
            ->shouldReceive('save')
            ->with($mockTeam)
            ->once()
            ->getMock();

        $this->repoMap['Printer']
            ->shouldReceive('fetchById')
            ->with(3)
            ->andReturn('bar')
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals(1, $res['id']['team']);
        $this->assertEquals(['Team updated successfully'], $res['messages']);
    }

    public function testHandleCommandWithVaidationException(): void
    {
        $this->expectException(ValidationException::class);

        $command = Cmd::create(
            [
                'id' => 1,
                'name' => 'foo',
                'description' => 'bar',
                'trafficArea' => 5,
                'defaultPrinter' => 3
            ]
        );

        $mockTeam = m::mock()
            ->shouldReceive('getId')
            ->andReturn(2)
            ->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchByName')
            ->with('foo')
            ->once()
            ->andReturn([$mockTeam])
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
