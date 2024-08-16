<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;

class FeeCreator
{
    /**
     * Create service instance
     *
     *
     * @return FeeCreator
     */
    public function __construct(private readonly FeeTypeRepository $feeTypeRepo, private readonly CommandCreator $commandCreator, private readonly CommandHandlerManager $commandHandlerManager, private readonly CurrentDateTimeFactory $currentDateTimeFactory)
    {
    }

    /**
     * Cancel any existing fees as appropriate and create new fees
     *
     * @param int $permitsRequired
     */
    public function create(IrhpApplicationEntity $irhpApplication, $permitsRequired)
    {
        $feeCommands = [];

        $outstandingIssueFees = $irhpApplication->getOutstandingIrfoPermitFees();

        foreach ($outstandingIssueFees as $fee) {
            $feeCommands[] = $this->commandCreator->create(
                CancelFee::class,
                ['id' => $fee->getId()]
            );
        }

        $feeType = $this->feeTypeRepo->getLatestByProductReference(
            FeeType::FEE_TYPE_ECMT_REMOVAL_ISSUE_PRODUCT_REF
        );

        $feeDescription = sprintf(
            '%s - %d permits',
            $feeType->getDescription(),
            $permitsRequired
        );

        $currentDateTime = $this->currentDateTimeFactory->create();

        $feeCommands[] = $this->commandCreator->create(
            CreateFee::class,
            [
                'licence' => $irhpApplication->getLicence()->getId(),
                'irhpApplication' => $irhpApplication->getId(),
                'invoicedDate' => $currentDateTime->format('Y-m-d'),
                'description' => $feeDescription,
                'feeType' => $feeType->getId(),
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'quantity' => $permitsRequired
            ]
        );

        foreach ($feeCommands as $command) {
            $this->commandHandlerManager->handleCommand($command, false);
        }
    }
}
