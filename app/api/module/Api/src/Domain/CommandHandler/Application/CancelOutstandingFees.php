<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;

/**
 * CancelOutstandingFees
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CancelOutstandingFees extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Fee';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $fees = $this->getRepo()->fetchOutstandingFeesByApplicationId($command->getId());

        /* @var $fee \Dvsa\Olcs\Api\Entity\Fee\Fee */
        foreach ($fees as $fee) {
            if ($fee->isFullyOutstanding()) {
                $result->merge($this->handleSideEffect(CancelFee::create(['id' => $fee->getId()])));
            }
        }

        $result->addMessage('CancelOutstandingFees success');
        return $result;
    }
}
