<?php

namespace Dvsa\Olcs\Cli\Command\Queue;

use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Consumer\CompaniesHouse\CompanyProfileDlq;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyProfileDlqSQSQueueCommand extends AbstractSQSCommand
{
    protected static $defaultName = 'queue:company-profile-dlq';

    #[\Override]
    protected function configure()
    {
        $this->setDescription('Processes the Company Profile DLQ (Dead Letter Queue) items.')
            ->setHelp('This command allows you to process items in the Company Profile Dead Letter Queue...');
        parent::configure();
    }

    protected function getCommandDto()
    {
        return CompanyProfileDlq::create([]);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $this->logAndWriteVerboseMessage("<info>Starting to process 'Company Profile DLQ' queue...</info>");
        return parent::execute($input, $output);
    }
}
