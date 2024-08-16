<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;

/**
 * Undo cancel all interim fees
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class UndoCancelAllInterimFees extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Fee';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $fees = $this->getRepo()->fetchInterimFeesByApplicationId($command->getId());

        /* @var $fee \Dvsa\Olcs\Api\Entity\Fee\Fee */
        foreach ($fees as $fee) {
            if ($fee->isCancelled()) {
                $fee->setFeeStatus($this->getRepo()->getRefdataReference(FeeEntity::STATUS_OUTSTANDING));
                $this->getRepo()->save($fee);
            }
        }

        $this->result->addMessage('All existing cancelled interim fees set back to outstanding');
        return $this->result;
    }
}
