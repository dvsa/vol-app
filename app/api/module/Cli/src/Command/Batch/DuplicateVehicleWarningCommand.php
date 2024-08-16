<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleWarnings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DuplicateVehicleWarningCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:duplicate-vehicle-warning';

    protected function configure()
    {
        $this->setDescription('Send duplicate vehicle warning letters');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([ProcessDuplicateVehicleWarnings::create([])]);

        return $this->outputResult(
            $result,
            'Successfully sent duplicate vehicle warnings',
            'Failed to send duplicate vehicle warnings'
        );
    }
}
