<?php

/**
 * Delete Operating Centre Condition Undertakings
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OperatingCentre;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteConditionUndertakings as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Operating Centre Condition Undertakings
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class DeleteConditionUndertakings extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ConditionUndertaking';

    /**
     * @param Cmd $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var OperatingCentreEntity $operatingCentre */
        $operatingCentre = $command->getOperatingCentre();

        $criteria = Criteria::create();

        if ($command->getApplication()) {
            if (!$command->getApplication()->isUnderConsideration()) {
                // return early, we only want to delete where application.status = Under consideration
                return $this->result;
            }

            $criteria->where($criteria->expr()->eq('application', $command->getApplication()));
        } elseif ($command->getLicence()) {
            $criteria->where($criteria->expr()->eq('licence', $command->getLicence()));
        }

        $count = 0;
        foreach ($operatingCentre->getConditionUndertakings()->matching($criteria) as $cu) {
            $this->getRepo()->delete($cu);
            $count++;
        }

        $this->result->addMessage(
            sprintf(
                "%d Condition/Undertaking(s) removed for Operating Centre %d",
                $count,
                $operatingCentre->getId()
            )
        );

        return $this->result;
    }
}
