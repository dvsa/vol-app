<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Organisation\FixIsIrfo;
use Dvsa\Olcs\Api\Domain\Command\Organisation\FixIsUnlicenced;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseMaintenanceCommand extends AbstractBatchCommand
{
    protected function configure()
    {
        $this
            ->setName('batch:database-maintenance')
            ->setDescription('Perform database management tasks, e.g., changing is_irfo flags');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([
            FixIsIrfo::create([]),
            FixIsUnlicenced::create([]),
        ]);

        return $this->outputResult(
            $result,
            'Database maintenance tasks completed successfully.',
            'An error occurred during database maintenance tasks.'
        );
    }
}
