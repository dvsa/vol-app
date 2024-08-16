<?php

namespace Dvsa\Olcs\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\CloseExpiredWindows;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CloseExpiredWindowsCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'permits:close-expired-windows';

    protected function configure()
    {
        $this->setDescription('Close all recently expired permit windows.')
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Date since when to close expired windows', '-1 day');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $params = ['since' => $input->getOption('since')];
        $result = $this->handleCommand([CloseExpiredWindows::create($params)]);

        return $this->outputResult(
            $result,
            'Successfully closed expired permit windows.',
            'Failed to close expired permit windows.'
        );
    }
}
