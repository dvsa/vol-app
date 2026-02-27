<?php

declare(strict_types=1);

/**
 * Update TeamPrinter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TeamPrinter;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TeamPrinter\UpdateTeamPrinter as UpdateTeamPrinter;
use Dvsa\Olcs\Api\Domain\Repository\TeamPrinter as TeamPrinterRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\TeamPrinter\UpdateTeamPrinter as Cmd;
use Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter as TeamPrinterEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Update Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateTeamPrinterTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateTeamPrinter();
        $this->mockRepo('TeamPrinter', TeamPrinterRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            TeamEntity::class => [
                1 => m::mock(TeamEntity::class)
            ],
            PrinterEntity::class => [
                2 => m::mock(PrinterEntity::class)
            ],
            UserEntity::class => [
                3 => m::mock(UserEntity::class)
            ],
            SubCategoryEntity::class => [
                4 => m::mock(SubCategoryEntity::class)
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
                'team' => 1,
                'printer' => 2,
                'user' => 3,
                'subCategory' => 4
            ]
        );

        $mockTeamPrinter = m::mock(TeamEntity::class)
            ->shouldReceive('getId')
            ->andReturn(1)
            ->shouldReceive('setTeam')
            ->with($this->references[TeamEntity::class][1])
            ->once()
            ->shouldReceive('setPrinter')
            ->with($this->references[PrinterEntity::class][2])
            ->once()
            ->shouldReceive('setUser')
            ->with($this->references[UserEntity::class][3])
            ->once()
            ->shouldReceive('setSubCategory')
            ->with($this->references[SubCategoryEntity::class][4])
            ->once()
            ->getMock();

        $this->repoMap['TeamPrinter']
            ->shouldReceive('fetchByDetails')
            ->once()
            ->andReturn([$mockTeamPrinter])
            ->shouldReceive('fetchById')
            ->with(1, \Doctrine\ORM\Query::HYDRATE_OBJECT, 2)
            ->andReturn($mockTeamPrinter)
            ->shouldReceive('save')
            ->with($mockTeamPrinter)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals(1, $res['id']['team']);
        $this->assertEquals(['Printer exception updated successfully'], $res['messages']);
    }

    public function testHandleCommandWithNoUserAndSubCategory(): void
    {
        $command = Cmd::create(
            [
                'id' => 1,
                'version' => 2,
                'team' => 1,
                'printer' => 2,
                'user' => null,
                'subCategory' => null
            ]
        );

        $mockTeamPrinter = m::mock(TeamEntity::class)
            ->shouldReceive('getId')
            ->andReturn(1)
            ->shouldReceive('setTeam')
            ->with($this->references[TeamEntity::class][1])
            ->once()
            ->shouldReceive('setPrinter')
            ->with($this->references[PrinterEntity::class][2])
            ->once()
            ->shouldReceive('setUser')
            ->with(null)
            ->once()
            ->shouldReceive('setSubCategory')
            ->with(null)
            ->once()
            ->getMock();

        $this->repoMap['TeamPrinter']
            ->shouldReceive('fetchByDetails')
            ->once()
            ->andReturn([])
            ->shouldReceive('fetchById')
            ->with(1, \Doctrine\ORM\Query::HYDRATE_OBJECT, 2)
            ->andReturn($mockTeamPrinter)
            ->shouldReceive('save')
            ->with($mockTeamPrinter)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals(1, $res['id']['team']);
        $this->assertEquals(['Printer exception updated successfully'], $res['messages']);
    }

    public function testHandleCommandWithVaidationException(): void
    {
        $this->expectException(ValidationException::class);

        $command = Cmd::create(
            [
                'id' => 1,
                'version' => 2,
                'team' => 1,
                'printer' => 2,
                'user' => 3,
                'subCategory' => 4
            ]
        );

        $mockTeamPrinter = m::mock()
            ->shouldReceive('getId')
            ->andReturn(2)
            ->getMock();

        $this->repoMap['TeamPrinter']
            ->shouldReceive('fetchByDetails')
            ->once()
            ->andReturn([$mockTeamPrinter])
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
