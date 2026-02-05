<?php

declare(strict_types=1);

/**
 * Update Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Printer;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Printer\UpdatePrinter as UpdatePrinter;
use Dvsa\Olcs\Api\Domain\Repository\Printer as PrinterRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Printer\UpdatePrinter as Cmd;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;

/**
 * Update Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdatePrinterTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdatePrinter();
        $this->mockRepo('Printer', PrinterRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'printerName' => 'foo',
                'description' => 'bar'
            ]
        );

        $mockPrinter = m::mock(PrinterEntity::class)
            ->shouldReceive('setPrinterName')
            ->with('foo')
            ->once()
            ->shouldReceive('setDescription')
            ->with('bar')
            ->once()
            ->shouldReceive('getId')
            ->andReturn(111)
            ->once()
            ->getMock();

        $this->repoMap['Printer']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->andReturn($mockPrinter)
            ->shouldReceive('save')
            ->with($mockPrinter)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['printer']);
        $this->assertEquals('Printer updated successfully', $res['messages'][0]);
    }
}
