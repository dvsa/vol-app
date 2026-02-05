<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Correspondence\ProcessInboxDocuments;
use Dvsa\Olcs\Cli\Command\Batch\ProcessInboxDocumentsCommand;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProcessInboxDocumentsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return ProcessInboxDocumentsCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:process-inbox-documents';
    }

    protected function getCommandDTOs(): array
    {
        return[ProcessInboxDocuments::create([])];
    }
}
