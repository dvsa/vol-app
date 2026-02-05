<?php

namespace Dvsa\Olcs\Cli\Command\Queue;

use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Consumer\CompaniesHouse\CompanyProfile;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCompanyProfileSQSQueueCommand extends AbstractSQSCommand
{
    protected static $defaultName = 'queue:process-company-profile';

    #[\Override]
    protected function configure()
    {
        $this->setDescription('Processes the Company Profile queue items.')
            ->setHelp('This command allows you to process items in the Company Profile queue...');
        parent::configure();
    }

    protected function getCommandDto()
    {
        return CompanyProfile::create([]);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $this->logAndWriteVerboseMessage("<info>Starting to process 'Company Profile' queue...</info>");
        return parent::execute($input, $output);
    }
}
