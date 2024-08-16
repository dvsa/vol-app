<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Correspondence\ProcessInboxDocuments;
use Dvsa\Olcs\Cli\Command\Batch\ProcessInboxDocumentsCommand;

class ProcessInboxDocumentsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return ProcessInboxDocumentsCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:process-inbox-documents';
    }

    protected function getCommandDTOs()
    {
        return[ProcessInboxDocuments::create([])];
    }
}
