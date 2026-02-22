<?php

namespace Dvsa\Olcs\Cli\Command\Queue;

use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Consumer\CompaniesHouse\ProcessInsolvencyDlq;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessInsolvencyDlqSQSQueueCommand extends AbstractSQSCommand
{
    protected static $defaultName = 'queue:process-insolvency-dlq';

    #[\Override]
    protected function configure()
    {
        $this->setDescription('Processes the Process Insolvency DLQ (Dead Letter Queue) items.')
            ->setHelp('This command allows you to process items in the Process Insolvency Dead Letter Queue...');
        parent::configure();
    }

    protected function getCommandDto()
    {
        return ProcessInsolvencyDlq::create([]);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $this->logAndWriteVerboseMessage("<info>Starting to process 'Process Insolvency DLQ' queue...</info>");
        return parent::execute($input, $output);
    }
}
