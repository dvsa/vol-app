<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\FirstTmLetter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FirstTmLetterCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:first-tm-letter';

    #[\Override]
    protected function configure(): void
    {
        $this->setDescription('Send first TM letters.');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $result = $this->handleCommand([FirstTmLetter::create([])]);

        return $this->outputResult(
            $result,
            'Successfully sent First TM letters.',
            'Failed to send First TM letters.'
        );
    }
}
