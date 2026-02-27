<?php

declare(strict_types=1);

/**
 * Create TeamPrinter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TeamPrinter;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TeamPrinter\CreateTeamPrinter as CreateTeamPrinter;
use Dvsa\Olcs\Api\Domain\Repository\TeamPrinter as TeamPrinterRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\TeamPrinter\CreateTeamPrinter as Cmd;
use Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter as TeamPrinterEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTeamPrinterTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateTeamPrinter();
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
                'team' => 1,
                'printer' => 2,
                'user' => 3,
                'subCategory' => 4
            ]
        );

        $teamPrinter = null;
        $this->repoMap['TeamPrinter']
            ->shouldReceive('fetchByDetails')
            ->once()
            ->andReturn([])
            ->shouldReceive('save')
            ->once()
            ->with(m::type(TeamPrinterEntity::class))
            ->andReturnUsing(
                function (TeamPrinterEntity $tp) use (&$teamPrinter) {
                    $tp->setId(111);
                    $teamPrinter = $tp;
                }
            )
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
                'team' => 1,
                'printer' => 2,
                'user' => 3,
                'subCategory' => 4
            ]
        );

        $this->repoMap['TeamPrinter']
            ->shouldReceive('fetchByDetails')
            ->once()
            ->andReturn(['foo'])
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
