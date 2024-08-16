<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete a list of FinancialStandingRates
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class DeleteFinancialStandingRateList extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'FinancialStandingRate';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $rates = $this->getRepo()->fetchByIds($command->getIds());

        /* @var $rate \Dvsa\Olcs\Api\Entity\System\FinancialStandingRate */
        foreach ($rates as $rate) {
            $this->getRepo()->delete($rate);
            $result->addMessage("Financial Standing Rate ID {$rate->getId()} deleted");
        }

        return $result;
    }
}
