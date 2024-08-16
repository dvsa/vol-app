<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\LastTmLetter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LastTmLetterCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:last-tm-letter';

    protected function configure()
    {
        $this->setDescription('Send Last TM letters.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([LastTmLetter::create([])]);

        return $this->outputResult(
            $result,
            'Successfully sent Last TM letters.',
            'Failed to send Last TM letters.'
        );
    }
}
