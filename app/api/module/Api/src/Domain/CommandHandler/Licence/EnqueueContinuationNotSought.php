<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\Command\Licence\EnqueueContinuationNotSought as Cmd;

/**
 * Enqueue licence CNS jobs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class EnqueueContinuationNotSought extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Queue';

    /**
     * Handle command
     *
     * @param Cmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $licences = $command->getLicences();
        $repo = $this->getRepo();
        $rows = $repo->enqueueContinuationNotSought($licences);
        $this->result->addMessage('Enqueued ' . $rows . ' CNS messages');

        $queue = new Queue();
        $queue->setStatus($repo->getRefdataReference(Queue::STATUS_QUEUED));
        $queue->setType($repo->getRefdataReference(Queue::TYPE_CNS_EMAIL));
        $options = [
            'licences' => $licences,
            'date' => $command->getDate()
        ];
        $queue->setOptions(json_encode($options));
        $this->getRepo()->save($queue);

        $this->result->addMessage('Send CNS email message enqueued');

        return $this->result;
    }
}
