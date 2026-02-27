<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\ProcessInboxDocumentsCommand;
use Dvsa\Olcs\Cli\Command\Batch\RemoveReadAuditCommand;
use Dvsa\Olcs\Cli\Domain\Command\RemoveReadAudit;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class RemoveReadAuditCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return RemoveReadAuditCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:remove-read-audit';
    }

    protected function getCommandDTOs(): array
    {
        return[RemoveReadAudit::create([])];
    }
}
