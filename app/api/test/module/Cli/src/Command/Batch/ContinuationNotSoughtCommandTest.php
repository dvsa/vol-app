<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Licence\EnqueueContinuationNotSought;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Query\Licence\ContinuationNotSoughtList;
use Dvsa\Olcs\Cli\Command\Batch\ContinuationNotSoughtCommand;
use Symfony\Component\Console\Command\Command;

class ContinuationNotSoughtCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return ContinuationNotSoughtCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:continuation-not-sought';
    }

    protected function getCommandDTOs()
    {
        return [
            EnqueueContinuationNotSought::create(['date' => new DateTime()]),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockQueryHandlerManager->method('handleQuery')
            ->willReturnCallback(fn($query) => ['count' => 2, 'result' => ['licence1', 'licence2']]);
    }

    public function testExecuteWithDryRun()
    {
        $this->mockCommandHandlerManager->expects($this->never())
            ->method('handleCommand');

        $this->executeCommand(['--dry-run' => true]);
    }

    public function testExecuteSuccess()
    {
        $this->mockCommandHandlerManager->expects($this->once())
            ->method('handleCommand')
            ->with($this->isInstanceOf(EnqueueContinuationNotSought::class))
            ->willReturn(new Result());

        $this->executeCommand(['--dry-run' => false]);
    }
}
