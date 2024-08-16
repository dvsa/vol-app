<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Transfer\Command\Task\FlagUrgentTasks;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FlagUrgentTasksCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:flag-urgent-tasks';

    protected function configure()
    {
        $this->setDescription('Flag tasks as urgent');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([FlagUrgentTasks::create([])]);

        return $this->outputResult(
            $result,
            'Successfully flagged tasks as urgent',
            'Failed to flag tasks as urgent'
        );
    }
}
