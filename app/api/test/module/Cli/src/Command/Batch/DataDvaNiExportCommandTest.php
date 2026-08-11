<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Cli\Command\Batch\DataDvaNiExportCommand;
use Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class DataDvaNiExportCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return DataDvaNiExportCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:data-dva-ni-export';
    }

    protected function getCommandDTOs(): array
    {
        return [
            DataDvaNiExport::create([
                'reportName' => 'exampleReport'
            ]),
        ];
    }

    #[\Override]
    public function testExecuteSuccess(): void
    {
        $params = [
            'reportName' => 'exampleReport'
        ];

        $this->mockCommandHandlerManager->expects($this->once())
            ->method('handleCommand')
            ->willReturnCallback(function ($command) use ($params): \Dvsa\Olcs\Api\Domain\Command\Result {
                $this->assertInstanceOf(\Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport::class, $command);
                $this->assertSame($params['reportName'], $command->getReportName());
                return new Result();
            });

        $this->executeCommand([
            '--report-name' => $params['reportName']
        ]);
    }
}
