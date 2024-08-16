<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\UpdateFinances as Command;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;

/**
 * Update Finances part of ContinuationDetail
 */
final class UpdateFinances extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    /**
     * Handle command
     *
     * @param CommandInterface $command DTO
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Command */

        /* @var $continuationDetail ContinuationDetail */
        $continuationDetail = $this->getRepo()->fetchById(
            $command->getId(),
            \Doctrine\ORM\Query::HYDRATE_OBJECT,
            $command->getVersion()
        );
        if ($command->getAverageBalanceAmount() !== null) {
            $continuationDetail->setAverageBalanceAmount($command->getAverageBalanceAmount());
        }
        if ($command->getHasOverdraft() !== null) {
            $continuationDetail->setHasOverdraft($command->getHasOverdraft());
        }
        if ($command->getHasOverdraft() !== null) {
            if ($command->getHasOverdraft() === 'Y') {
                $continuationDetail->setOverdraftAmount($command->getOverdraftAmount());
            } else {
                $continuationDetail->setOverdraftAmount(null);
            }
        }
        if ($command->getHasFactoring() !== null) {
            $continuationDetail->setHasFactoring($command->getHasFactoring());
            if ($command->getHasFactoring() === 'Y') {
                $continuationDetail->setFactoringAmount($command->getFactoringAmount());
            } else {
                $continuationDetail->setFactoringAmount(null);
            }
        }
        if ($command->getHasOtherFinances() !== null) {
            $continuationDetail->setHasOtherFinances($command->getHasOtherFinances());
            if ($command->getHasOtherFinances() === 'Y') {
                $continuationDetail->setOtherFinancesAmount($command->getOtherFinancesAmount());
                $continuationDetail->setOtherFinancesDetails($command->getOtherFinancesDetails());
            } else {
                $continuationDetail->setOtherFinancesAmount(null);
                $continuationDetail->setOtherFinancesDetails(null);
            }
        }

        $this->getRepo()->save($continuationDetail);

        $result = new Result();
        $result->addId('continuationDetail', $continuationDetail->getId());
        $result->addMessage('ContinuationDetail finances updated');

        return $result;
    }
}
