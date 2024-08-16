<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleRemoval;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DuplicateVehicleRemovalCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:duplicate-vehicle-removal';

    protected function configure()
    {
        $this->setDescription('Duplicate vehicle removal');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([ProcessDuplicateVehicleRemoval::create([])]);

        return $this->outputResult(
            $result,
            'Successfully removed duplicate vehicles',
            'Failed to remove duplicate vehicles'
        );
    }
}
