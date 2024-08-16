<?php

namespace Dvsa\Olcs\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkExpiredPermits;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MarkExpiredPermitsCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'permits:mark-expired-permits';

    protected function configure()
    {
        $this
            ->setDescription('Mark permits as expired.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $result = $this->handleCommand([MarkExpiredPermits::create([])]);

        return $this->outputResult(
            $result,
            'Successfully marked permits as expired.',
            'Failed to mark permits as expired.'
        );
    }
}
