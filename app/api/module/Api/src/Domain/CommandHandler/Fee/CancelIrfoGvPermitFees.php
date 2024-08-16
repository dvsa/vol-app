<?php

/**
 * Cancel Irfo Gv Permit Fees
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Cancel Irfo Gv Permit Fees
 */
final class CancelIrfoGvPermitFees extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Fee';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $fees = $this->getRepo()->fetchFeesByIrfoGvPermitId($command->getId());

        /* @var $fee \Dvsa\Olcs\Api\Entity\Fee\Fee */
        foreach ($fees as $fee) {
            $result->merge(
                $this->handleSideEffect(
                    CancelFeeCommand::create(['id' => $fee->getId()])
                )
            );
        }

        $result->addMessage('IRFO GV Permit fees cancelled successfully');

        return $result;
    }
}
