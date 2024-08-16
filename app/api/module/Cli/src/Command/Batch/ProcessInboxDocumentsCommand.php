<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Correspondence\ProcessInboxDocuments;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessInboxDocumentsCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:process-inbox-documents';

    protected function configure()
    {
        $this->setDescription('Process inbox documents.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([ProcessInboxDocuments::create([])]);
        return $this->outputResult(
            $result,
            'Successfully processed inbox documents.',
            'Failed to process inbox documents.'
        );
    }
}
