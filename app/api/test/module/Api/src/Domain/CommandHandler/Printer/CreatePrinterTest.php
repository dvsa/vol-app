<?php

declare(strict_types=1);

/**
 * Create Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Printer;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Printer\CreatePrinter as CreatePrinter;
use Dvsa\Olcs\Api\Domain\Repository\Printer as PrinterRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Printer\CreatePrinter as Cmd;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as PrinterEntity;

/**
 * Create Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreatePrinterTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreatePrinter();
        $this->mockRepo('Printer', PrinterRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(
            [
                'printerName' => 'foo',
                'description' => 'bar'
            ]
        );

        $printer = null;
        $this->repoMap['Printer']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PrinterEntity::class))
            ->andReturnUsing(
                function (PrinterEntity $pr) use (&$printer) {
                    $pr->setId(111);
                    $printer = $pr;
                }
            )
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['printer']);
    }
}
