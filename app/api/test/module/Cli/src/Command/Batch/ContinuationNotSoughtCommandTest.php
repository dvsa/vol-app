<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Licence\EnqueueContinuationNotSought;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Query\Licence\ContinuationNotSoughtList;
use Dvsa\Olcs\Cli\Command\Batch\ContinuationNotSoughtCommand;
use Symfony\Component\Console\Command\Command;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ContinuationNotSoughtCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return ContinuationNotSoughtCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:continuation-not-sought';
    }

    protected function getCommandDTOs(): array
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

    public function testExecuteWithDryRun(): void
    {
        $this->mockCommandHandlerManager->expects($this->never())
            ->method('handleCommand');

        $this->executeCommand(['--dry-run' => true]);
    }

    #[\Override]
    public function testExecuteSuccess(): void
    {
        $this->mockCommandHandlerManager->expects($this->once())
            ->method('handleCommand')
            ->with($this->isInstanceOf(EnqueueContinuationNotSought::class))
            ->willReturn(new Result());

        $this->executeCommand(['--dry-run' => false]);
    }
}
