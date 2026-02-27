<?php

declare(strict_types=1);

/**
 * Delete TeamPrinter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Printer;

use Dvsa\Olcs\Api\Domain\CommandHandler\TeamPrinter\DeleteTeamPrinter as DeleteTeamPrinter;
use Dvsa\Olcs\Api\Domain\Repository\TeamPrinter as TeamPrinterRepo;
use Dvsa\Olcs\Transfer\Command\TeamPrinter\DeleteTeamPrinter as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Delete TeamPrinter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DeleteTeamPrinterTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteTeamPrinter();
        $this->mockRepo('TeamPrinter', TeamPrinterRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(
            [
                'id' => 111
            ]
        );

        $this->repoMap['TeamPrinter']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->andReturn('tp')
            ->shouldReceive('delete')
            ->with('tp')
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals('TeamPrinter deleted', $res['messages'][0]);
    }
}
