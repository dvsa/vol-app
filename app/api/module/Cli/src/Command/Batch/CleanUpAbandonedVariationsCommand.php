<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\CleanUpAbandonedVariations;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUpAbandonedVariationsCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:clean-up-variations';

    protected function configure()
    {
        $this->setDescription('Clean up abandoned variations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([CleanUpAbandonedVariations::create([])]);

        if ($result) {
            $this->logAndWriteVerboseMessage('<error>Failed to clean up abandoned variations.</error>');
            return Command::FAILURE;
        }

        $this->logAndWriteVerboseMessage('<info>Successfully cleaned up abandoned variations.</info>');
        return Command::SUCCESS;
    }
}
