<?php

/**
 * Cancel Licence Fees
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Cancel Licence Fees
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CancelLicenceFees extends AbstractCommandHandler
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $fees = $licence->getFees()->filter(
            fn($element) => in_array($element->getFeeStatus(), [Fee::STATUS_OUTSTANDING])
        );

        if ($fees->isEmpty()) {
            $result->addMessage('No fees to remove');
            return $result;
        }

        /** @var Fee $fee */
        foreach ($fees as $fee) {
            $result->merge(
                $this->handleSideEffect(CancelFeeCmd::create(['id' => $fee->getId()]))
            );
        }

        $result->addMessage(count($fees) . ' fee(s) cancelled');
        return $result;
    }
}
