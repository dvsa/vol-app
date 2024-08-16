<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplications as EndIrhpApplicationsCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CancelApplication;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Withdraw;

/**
 * End IRHP applications relating to a licence
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EndIrhpApplications extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    /**
     * Handle command
     *
     * @param EndIrhpApplicationsCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $licenceId = $command->getId();
        $licence = $this->getRepo()->fetchById($licenceId);

        foreach ($licence->getOngoingIrhpApplications() as $irhpApplication) {
            switch ($irhpApplication->getStatus()->getId()) {
                case IrhpInterface::STATUS_NOT_YET_SUBMITTED:
                    $this->result->merge(
                        $this->handleSideEffect(
                            CancelApplication::create(['id' => $irhpApplication->getId()])
                        )
                    );
                    break;
                case IrhpInterface::STATUS_UNDER_CONSIDERATION:
                case IrhpInterface::STATUS_AWAITING_FEE:
                    $this->result->merge(
                        $this->handleSideEffect(
                            Withdraw::create(
                                [
                                    'id' => $irhpApplication->getId(),
                                    'reason' => $command->getReason(),
                                ]
                            )
                        )
                    );
                    break;
            }
        }

        $this->result->addMessage('Cleared IRHP applications for licence ' . $licenceId);

        return $this->result;
    }
}
