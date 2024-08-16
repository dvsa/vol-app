<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Transfer\Command\Permits\QueueRunScoring as QueueRunScoringCmd;
use Dvsa\Olcs\Api\Domain\Query\Permits\QueueRunScoringPermitted as QueueRunScoringPermittedQry;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Queue run scoring
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class QueueRunScoring extends AbstractCommandHandler
{
    use QueueAwareTrait;

    protected $repoServiceName = 'IrhpPermitStock';

    /**
     * Handle command
     *
     * @param QueueRunScoringCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $stockId = $command->getId();

        $permittedResult = $this->handleQuery(
            QueueRunScoringPermittedQry::create(['id' => $stockId])
        );

        if (!$permittedResult['result']) {
            $this->result->addMessage('Unable to queue run scoring: ' . $permittedResult['message']);
            return $this->result;
        }

        $stockRepo = $this->getRepo();
        $stock = $stockRepo->fetchById($stockId);
        $stock->proceedToScoringPending($this->refData(IrhpPermitStock::STATUS_SCORING_PENDING));
        $stockRepo->save($stock);

        $this->result->merge(
            $this->handleSideEffect(
                $this->createQueue($stockId, Queue::TYPE_RUN_ECMT_SCORING, ['deviation' => $command->getDeviation()])
            )
        );

        $this->result->addMessage('Queueing run scoring of ECMT applications');
        return $this->result;
    }
}
