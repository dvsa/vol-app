<?php

/**
 * CancelFee
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks as CloseTasksCmd;

/**
 * CancelFee
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CancelFee extends AbstractCommandHandler
{
    protected $repoServiceName = 'Fee';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $fee Fee */
        $fee = $this->getRepo()->fetchUsingId($command);
        $fee->setFeeStatus($this->getRepo()->getRefdataReference(Fee::STATUS_CANCELLED));
        $this->getRepo()->save($fee);

        $this->result->addMessage('Fee ' . $fee->getId() . ' cancelled successfully');

        $this->maybeCloseFeeTask($fee);

        return $this->result;
    }

    /**
     * If the fee has an associated task, close it
     */
    protected function maybeCloseFeeTask(Fee $fee)
    {
        if ($fee->getTask()) {
            $taskIdsToClose = [$fee->getTask()->getId()];
            $this->result->merge(
                $this->handleSideEffect(CloseTasksCmd::create(['ids' => $taskIdsToClose]))
            );
        }
    }
}
