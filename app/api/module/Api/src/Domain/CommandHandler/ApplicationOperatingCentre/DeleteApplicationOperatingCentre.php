<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationOperatingCentre;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Class CreateApplicationOperatingCentre
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationOperatingCentre
 */
final class DeleteApplicationOperatingCentre extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ApplicationOperatingCentre';

    /**
     * Delete application operating centres.
     */
    public function handleCommand(CommandInterface $command)
    {
        $s4 = $this->getRepo()->getReference(S4::class, $command->getS4());
        $applicationOperatingCentres = $this->getRepo()->fetchByS4($s4->getId());

        foreach ($applicationOperatingCentres as $applicationOperatingCentre) {
            $this->getRepo()->delete($applicationOperatingCentre);
        }

        $result = new Result();
        $result->addMessage('Application operating centre(s) removed.');

        return $result;
    }
}
