<?php

namespace Dvsa\Olcs\Cli\Command\Queue;

use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Consumer\TransXChange\TransXChangeConsumer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransXChangeConsumerSQSQueueCommand extends AbstractSQSCommand
{
    protected static $defaultName = 'queue:transxchange-consumer';

    #[\Override]
    protected function configure()
    {
        $this->setDescription('Processes TransXChange queue items.')
            ->setHelp('This command allows you to process items in the TransXChange queue.');
        parent::configure();
    }

    protected function getCommandDto()
    {
        return TransXChangeConsumer::create([]);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $this->logAndWriteVerboseMessage("<info>Starting to process 'TransXChange' queue...</info>");
        return parent::execute($input, $output);
    }
}
