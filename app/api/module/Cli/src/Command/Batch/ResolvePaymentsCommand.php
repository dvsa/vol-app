<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolveOutstandingPayments;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResolvePaymentsCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:resolve-payments';

    protected function configure()
    {
        $this->setDescription('Resolve pending CPMS payments.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $result = $this->handleCommand([ResolveOutstandingPayments::create([])]);

        return $this->outputResult(
            $result,
            'Successfully resolved outstanding CPMS payments.',
            'Failed to resolve outstanding CPMS payments.'
        );
    }
}
