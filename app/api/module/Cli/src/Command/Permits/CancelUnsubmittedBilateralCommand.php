<?php

namespace Dvsa\Olcs\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\CancelUnsubmittedBilateral;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CancelUnsubmittedBilateralCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'batch:permits:cancel-unsubmitted-bilateral';

    protected function configure()
    {
        $this->setDescription('Cancel unsubmitted bilateral applications.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([CancelUnsubmittedBilateral::create([])]);
        return $this->outputResult(
            $result,
            'Successfully cancelled unsubmitted bilateral applications.',
            'Failed to cancel unsubmitted bilateral applications.'
        );
    }
}
