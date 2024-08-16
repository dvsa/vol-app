<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\DigitalSendReminders;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DigitalContinuationRemindersCommand extends AbstractBatchCommand
{
    protected function configure()
    {
        $this
            ->setName('batch:digital-continuation-reminders')
            ->setDescription('Generate digital continuation reminders.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([DigitalSendReminders::create([])]);

        return $this->outputResult(
            $result,
            'Successfully sent processed digital continuation reminders',
            'Failed to process digital continuation reminders'
        );
    }
}
