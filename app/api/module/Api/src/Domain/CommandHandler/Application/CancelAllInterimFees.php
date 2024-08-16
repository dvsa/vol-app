<?php

/**
 * Cancel All Interim Fees
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;

/**
 * Cancel All Interim Fees
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CancelAllInterimFees extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Fee';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $fees = $this->getRepo()->fetchInterimFeesByApplicationId($command->getId(), true);

        /* @var $fee \Dvsa\Olcs\Api\Entity\Fee\Fee */
        foreach ($fees as $fee) {
            if ($fee->isFullyOutstanding()) {
                $result->merge($this->handleSideEffect(CancelFee::create(['id' => $fee->getId()])));
            }
        }

        $result->addMessage('CancelAllInterimFees success');
        return $result;
    }
}
