<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\ProcessToRevokeCurtailSuspend;
use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\ProcessToValid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LicenceStatusRulesCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:licence-status-rules';

    protected function configure()
    {
        $this->setDescription('Process licence status change rules');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand(
            [
                ProcessToRevokeCurtailSuspend::create([]),
                ProcessToValid::create([])
            ]
        );

        return $this->outputResult(
            $result,
            'Successfully processed licence status rules',
            'Failed to process licence status rules'
        );
    }
}
