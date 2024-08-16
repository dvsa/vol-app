<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\InterimEndDateEnforcement;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InterimEndDateEnforcementCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:interim-end-date-enforcement';

    protected function configure()
    {
        $this->setDescription('Enforces interim end date by checking applications under consideration with an in-force interim that have an end date of the previous day or earlier.');
        $this->addCommonOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initializeOutputInterface($output);

        $dryRun = $this->isDryRun($input);
        $result = $this->handleCommand([InterimEndDateEnforcement::create(['dryRun' => $dryRun])]);

        return $this->outputResult(
            $result,
            'Successfully enforced interim end dates.',
            'Failed to enforce interim end dates.'
        );
    }
}
