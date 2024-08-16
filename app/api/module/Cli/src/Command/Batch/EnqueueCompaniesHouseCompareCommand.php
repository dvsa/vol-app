<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Enqueue;
use Dvsa\Olcs\Cli\Domain\Query\CompaniesHouse\Organisations;
use Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\CompanyProfile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnqueueCompaniesHouseCompareCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:enqueue-companies-house-compare';

    protected function configure()
    {
        $this->setDescription('Enqueue Companies House lookup for all Organisations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $organisations = $this->handleQuery(Organisations::create([]));

        if (!$organisations) {
            $this->logAndWriteVerboseMessage('<error>Failed to retrieve organisations for Companies House comparison</error>');
            return Command::FAILURE;
        }

        $result = $this->handleCommand([Enqueue::create([
            'messageData' => $organisations,
            'queueType' => CompanyProfile::class,
            'messageType' => CompanyProfile::class,
        ])]);

        return $this->outputResult(
            $result,
            'Successfully enqueued Companies House comparison messages',
            'Failed to enqueue Companies House comparison messages'
        );
    }
}
