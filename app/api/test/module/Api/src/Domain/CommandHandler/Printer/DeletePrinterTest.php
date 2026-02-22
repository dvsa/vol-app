<?php

declare(strict_types=1);

/**
 * Delete Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Printer;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Printer\DeletePrinter as DeletePrinter;
use Dvsa\Olcs\Api\Domain\Repository\Printer as PrinterRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Printer\DeletePrinter as Cmd;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Delete Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DeletePrinterTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeletePrinter();
        $this->mockRepo('Printer', PrinterRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'validate' => false
            ]
        );

        $mockPrinter = m::mock(PrinterEntity::class)
            ->shouldReceive('canDelete')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getId')
            ->andReturn(111)
            ->once()
            ->getMock();

        $this->repoMap['Printer']
            ->shouldReceive('fetchWithTeams')
            ->once()
            ->andReturn($mockPrinter)
            ->shouldReceive('delete')
            ->with($mockPrinter)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['printer']);
        $this->assertEquals('Printer deleted successfully', $res['messages'][0]);
    }

    public function testHandleCommandValidateOnly(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'validate' => true
            ]
        );

        $mockPrinter = m::mock(PrinterEntity::class)
            ->shouldReceive('canDelete')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->repoMap['Printer']
            ->shouldReceive('fetchWithTeams')
            ->once()
            ->andReturn($mockPrinter)
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals('Ready to remove', $res['messages'][0]);
    }

    public function testHandleCommandWithException(): void
    {
        $this->expectException(ValidationException::class);

        $command = Cmd::create(
            [
                'id' => 111,
                'validate' => false
            ]
        );

        $mockPrinter = m::mock(PrinterEntity::class)
            ->shouldReceive('canDelete')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->repoMap['Printer']
            ->shouldReceive('fetchWithTeams')
            ->once()
            ->andReturn($mockPrinter)
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
