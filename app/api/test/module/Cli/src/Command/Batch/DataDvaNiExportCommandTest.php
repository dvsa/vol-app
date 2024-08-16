<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Cli\Command\Batch\DataDvaNiExportCommand;
use Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport;

class DataDvaNiExportCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return DataDvaNiExportCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:data-dva-ni-export';
    }

    protected function getCommandDTOs()
    {
        return [
            DataDvaNiExport::create([
                'reportName' => 'exampleReport',
                'path' => '/path/to/save'
            ]),
        ];
    }

    public function testExecuteSuccess()
    {
        $params = [
            'reportName' => 'exampleReport',
            'path' => '/path/to/save',
        ];

        $this->mockCommandHandlerManager->expects($this->once())
            ->method('handleCommand')
            ->with($this->callback(fn($command) => $command instanceof DataDvaNiExport
                && $command->getReportName() === $params['reportName']
                && $command->getPath() === $params['path']))
            ->willReturn(new Result());

        $this->executeCommand([
            '--report-name' => $params['reportName'],
            '--path' => $params['path'],
        ]);
    }
}
