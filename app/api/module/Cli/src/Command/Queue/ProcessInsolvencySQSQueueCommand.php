<?php

namespace Dvsa\Olcs\Cli\Command\Queue;

use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Consumer\CompaniesHouse\ProcessInsolvency;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessInsolvencySQSQueueCommand extends AbstractSQSCommand
{
    protected static $defaultName = 'queue:process-insolvency';

    #[\Override]
    protected function configure()
    {
        $this->setDescription('Processes the Process Insolvency queue items.')
            ->setHelp('This command allows you to process items in the Process Insolvency queue...');
        parent::configure();
    }

    protected function getCommandDto()
    {
        return ProcessInsolvency::create([]);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $this->logAndWriteVerboseMessage("<info>Starting to process 'Process Insolvency' queue...</info>");
        return parent::execute($input, $output);
    }
}
