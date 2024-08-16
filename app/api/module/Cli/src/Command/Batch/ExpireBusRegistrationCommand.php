<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\Bus\Expire;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireBusRegistrationCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:expire-bus-registration';

    protected function configure()
    {
        $this->setDescription('Expire bus registrations past their end date.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([Expire::create([])]);

        return $this->outputResult(
            $result,
            'Successfully expired bus registrations.',
            'Failed to expire bus registrations.'
        );
    }
}
