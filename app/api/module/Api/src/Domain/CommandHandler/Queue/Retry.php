<?php

/**
 * Retry queue item handler
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Queue;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class Retry extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Queue';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\Queue\Retry $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $processAfter = new DateTime();
        $processAfter->add(new \DateInterval(sprintf('PT%dS', $command->getRetryAfter())));

        $item = $command->getItem();
        $item->setStatus($this->getRepo()->getRefdataReference(QueueEntity::STATUS_QUEUED));
        $item->setProcessAfterDate($processAfter);
        $item->setLastError($command->getLastError());
        $this->getRepo()->save($item);

        $result = new Result();
        $result
            ->addId('queue', $item->getId())
            ->addMessage(sprintf('Queue item requeued for after %s', $processAfter->format(\DateTime::W3C)));

        return $result;
    }
}
