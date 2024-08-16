<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;

class ApplicationFeesClearer
{
    /**
     * Create service instance
     *
     *
     * @return ApplicationFeesClearer
     */
    public function __construct(private readonly CommandCreator $commandCreator, private readonly CommandHandlerManager $commandHandlerManager, private readonly FeeRepository $feeRepo)
    {
    }

    /**
     * Cancel all outstanding fees associated with this application
     */
    public function clear(IrhpPermitApplication $irhpPermitApplication)
    {
        $outstandingFees = $irhpPermitApplication->getOutstandingFees();
        foreach ($outstandingFees as $fee) {
            $command = $this->commandCreator->create(
                CancelFee::class,
                ['id' => $fee->getId()]
            );
            $this->commandHandlerManager->handleCommand($command, false);
        }

        $fees = $irhpPermitApplication->getFees();
        foreach ($fees as $fee) {
            $fee->removeIrhpPermitApplicationAssociation();
            $this->feeRepo->save($fee);
        }
    }
}
