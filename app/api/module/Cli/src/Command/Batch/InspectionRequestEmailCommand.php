<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Email\Domain\Command\ProcessInspectionRequestEmail;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InspectionRequestEmailCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:inspection-request-email';

    protected function configure()
    {
        $this->setDescription('Process inspection request email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([ProcessInspectionRequestEmail::create([])]);

        return $this->outputResult(
            $result,
            'Successfully Processed inspection request email',
            'Failed to Process inspection request email'
        );
    }
}
