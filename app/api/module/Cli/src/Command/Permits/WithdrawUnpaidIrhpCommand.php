<?php

namespace Dvsa\Olcs\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\WithdrawUnpaidIrhp;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WithdrawUnpaidIrhpCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'batch:permits:withdraw-unpaid';

    protected function configure()
    {
        $this->setDescription('Withdraw unpaid IRHP applications.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([WithdrawUnpaidIrhp::create([])]);
        return $this->outputResult(
            $result,
            'Successfully withdrew unpaid IRHP applications.',
            'Failed to withdraw unpaid IRHP applications.'
        );
    }
}
