<?php

namespace Dvsa\Olcs\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\ResetPermitsTestData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reset Permits Test Data Command
 *
 * CLI command to reset permits test data for VFT regression testing
 * in non-production environments.
 */
class ResetPermitsTestDataCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'batch:permits:reset-test-data';

    protected function configure()
    {
        $this->setDescription('Reset permits test data for VFT regression testing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([ResetPermitsTestData::create([])]);
        return $this->outputResult(
            $result,
            'Successfully reset permits test data.',
            'Failed to reset permits test data.'
        );
    }
}
