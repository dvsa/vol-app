<?php

/**
 * Cancel Irfo Psv Auth Fees
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Cancel Irfo Psv Auth Fees
 */
final class CancelIrfoPsvAuthFees extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Fee';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $fees = $this->getRepo()->fetchFeesByIrfoPsvAuthId($command->getId());

        /* @var $fee \Dvsa\Olcs\Api\Entity\Fee\Fee */
        foreach ($fees as $fee) {
            if (
                $fee->getFeeStatus()->getId() == Fee::STATUS_OUTSTANDING &&
                !in_array(
                    $fee->getFeeType()->getFeeType()->getId(),
                    $command->getExclusions()
                )
            ) {
                $result->merge(
                    $this->handleSideEffect(
                        CancelFeeCommand::create(['id' => $fee->getId()])
                    )
                );
            }
        }

        $result->addMessage('IRFO PSV Auth fees cancelled successfully');

        return $result;
    }
}
