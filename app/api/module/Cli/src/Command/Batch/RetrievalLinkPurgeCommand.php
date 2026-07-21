<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\RetrievalLink\PurgeExpired;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RetrievalLinkPurgeCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:retrieval-link-purge';

    #[\Override]
    protected function configure()
    {
        $this->setDescription('Delete expired Retrieve-via-Link links (members + OTP codes cascade; audit events retained).');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([PurgeExpired::create([])]);

        if ($result) {
            $this->logAndWriteVerboseMessage('<error>Failed to purge expired retrieval links.</error>');
            return Command::FAILURE;
        }

        $this->logAndWriteVerboseMessage('<info>Successfully purged expired retrieval links.</info>');
        return Command::SUCCESS;
    }
}
