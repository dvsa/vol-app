<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Licence\CreateSurrenderPsvLicenceTasks;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Query\Licence\PsvLicenceSurrenderList;
use Dvsa\Olcs\Cli\Command\Batch\CreatePsvLicenceSurrenderTasksCommand;

class CreatePsvLicenceSurrenderTasksCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return CreatePsvLicenceSurrenderTasksCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:create-psv-licence-surrender-tasks';
    }

    protected function getCommandDTOs()
    {
        return [
            PsvLicenceSurrenderList::create(['date' => new DateTime()]),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockQueryHandlerManager->method('handleQuery')
            ->willReturnCallback(function ($query) {
                if ($query instanceof PsvLicenceSurrenderList) {
                    return ['count' => 3, 'result' => ['id1', 'id2', 'id3']];
                }
                return ['count' => 0, 'result' => []];
            });
    }

    public function testExecuteWithDryRun()
    {
        $this->mockQueryHandlerManager->expects($this->once())
            ->method('handleQuery')
            ->willReturn(['count' => 3, 'result' => ['id1', 'id2', 'id3']]);

        $this->mockCommandHandlerManager->expects($this->never())
            ->method('handleCommand');

        $this->executeCommand(['--dry-run' => true]);
    }

    public function testExecuteSuccess()
    {
        $this->mockQueryHandlerManager->expects($this->once())
            ->method('handleQuery')
            ->willReturn(['count' => 3, 'result' => ['id1', 'id2', 'id3']]);

        $this->mockCommandHandlerManager->expects($this->once())
            ->method('handleCommand')
            ->with($this->isInstanceOf(CreateSurrenderPsvLicenceTasks::class))
            ->willReturn(new Result());

        $this->executeCommand(['--dry-run' => false]);
    }
}
