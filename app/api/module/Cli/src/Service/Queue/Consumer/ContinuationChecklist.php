<?php

/**
 * Continuation Checklist Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\Process as Cmd;
use Dvsa\Olcs\Api\Domain\Exception\Exception as DomainException;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\Update as UpdateContinuationDetail;

/**
 * Continuation Checklist Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ContinuationChecklist extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * @param QueueEntity $item
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        return ['id' => $item->getEntityId(), 'user' => $item->getCreatedBy()->getId()];
    }

    /**
     * Mark the continuation detail record as errored and then mark the queue
     * message as failed
     *
     * @param QueueEntity $item
     * @param string $reason
     * @return string
     */
    #[\Override]
    public function failed(QueueEntity $item, $reason = null)
    {
        $dtoData = [
            'id' => $item->getEntityId(),
            'status' => ContinuationDetailEntity::STATUS_ERROR,
        ];
        $command = UpdateContinuationDetail::create($dtoData);

        try {
            $this->handleCommand($command);
        } catch (DomainException $e) {
            $message = !empty($e->getMessages()) ? implode(', ', $e->getMessages()) : $e->getMessage();
            $reason .= ", " . $message;
        }

        return parent::failed($item, $reason);
    }
}
