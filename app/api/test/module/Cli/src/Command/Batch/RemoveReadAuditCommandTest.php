<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\ProcessInboxDocumentsCommand;
use Dvsa\Olcs\Cli\Command\Batch\RemoveReadAuditCommand;
use Dvsa\Olcs\Cli\Domain\Command\RemoveReadAudit;

class RemoveReadAuditCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return RemoveReadAuditCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:remove-read-audit';
    }

    protected function getCommandDTOs()
    {
        return[RemoveReadAudit::create([])];
    }
}
