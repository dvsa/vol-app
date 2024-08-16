<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\DataRetention\DeleteEntities;
use Dvsa\Olcs\Api\Domain\Command\DataRetention\Populate;
use Dvsa\Olcs\Api\Domain\Command\DataRetention\Precheck;
use Dvsa\Olcs\Api\Domain\Query\DataRetention\Postcheck;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DataRetentionCommand extends AbstractBatchCommand
{
    protected function configure()
    {
        $this
            ->setName('data-retention:run')
            ->setDescription('Run data retention rules')
            ->addOption('populate', null, InputOption::VALUE_NONE, 'Populate data retention records.')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete data retention records.')
            ->addOption('precheck', null, InputOption::VALUE_NONE, 'Precheck data retention records.')
            ->addOption('postcheck', null, InputOption::VALUE_NONE, 'Postcheck data retention records.')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit for delete and precheck.', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        if ($input->getOption('populate')) {
            return $this->handleCommand([Populate::create([])]);
        }

        $limit = (int) $input->getOption('limit');
        if ($input->getOption('delete')) {
            return $this->handleCommand([DeleteEntities::create(['limit' => $limit])]);
        }

        if ($input->getOption('precheck')) {
            return $this->handleCommand([Precheck::create(['limit' => $limit])]);
        }

        if ($input->getOption('postcheck')) {
            $result = $this->handleQuery(Postcheck::create([]));
            $this->logAndWriteVerboseMessage(json_encode($result, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        $this->logAndWriteVerboseMessage('No valid option specified.');
        return Command::FAILURE;
    }
}
