<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplicationsAndPermits as EndIrhpApplicationsAndPermitsCmd;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplications as EndIrhpApplicationsCmd;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpPermits as EndIrhpPermitsCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * End IRHP applications and permits relating to a licence
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EndIrhpApplicationsAndPermits extends AbstractCommandHandler implements TransactionedInterface
{
    /**
     * Handle command
     *
     * @param EndIrhpApplicationsAndPermitsCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $endIrhpApplicationsCmd = EndIrhpApplicationsCmd::create(
            [
                'id' => $command->getId(),
                'reason' => $command->getReason(),
            ]
        );

        $endIrhpPermitsCmd = EndIrhpPermitsCmd::create(
            [
                'id' => $command->getId(),
                'context' => $command->getContext()
            ]
        );

        $sideEffects = [
            $endIrhpApplicationsCmd,
            $endIrhpPermitsCmd,
        ];

        $this->result->merge(
            $this->handleSideEffects($sideEffects)
        );

        return $this->result;
    }
}
