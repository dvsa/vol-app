<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\RemoveReadAudit;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveReadAuditCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:remove-read-audit';

    protected function configure()
    {
        $this->setDescription('Process deletion of old read audit records');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([RemoveReadAudit::create([])]);

        return $this->outputResult(
            $result,
            'Successfully removed read audits',
            'Failed to remove read audits'
        );
    }
}
