<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Common;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\ApplicationFeesClearer;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;

class NoOfPermitsUpdater
{
    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsUpdater
     */
    public function __construct(private readonly IrhpPermitApplicationRepository $irhpPermitApplicationRepo, private readonly FeeTypeRepository $feeTypeRepo, private readonly CommandCreator $commandCreator, private readonly CommandHandlerManager $commandHandlerManager, private readonly ApplicationFeesClearer $applicationFeesClearer, private readonly CurrentDateTimeFactory $currentDateTimeFactory)
    {
    }

    /**
     * Update the number of permits answer and fees relating to a specific country within a bilateral application
     */
    public function update(IrhpPermitApplication $irhpPermitApplication, array $updatedAnswers)
    {
        $irhpPermitApplication->updateBilateralRequired($updatedAnswers);
        $this->irhpPermitApplicationRepo->save($irhpPermitApplication);
        $this->applicationFeesClearer->clear($irhpPermitApplication);

        $currentDateTime = $this->currentDateTimeFactory->create();
        $irhpApplication = $irhpPermitApplication->getIrhpApplication();

        $createFeeParameters = [
            'licence' => $irhpApplication->getLicence()->getId(),
            'irhpApplication' => $irhpApplication->getId(),
            'irhpPermitApplication' => $irhpPermitApplication->getId(),
            'invoicedDate' => $currentDateTime->format('Y-m-d'),
            'feeStatus' => Fee::STATUS_OUTSTANDING,
        ];

        $productRefsAndQuantities = $irhpPermitApplication->getBilateralFeeProductRefsAndQuantities();
        foreach ($productRefsAndQuantities as $productReference => $quantity) {
            $feeType = $this->feeTypeRepo->getLatestByProductReference($productReference);

            $createFeeParameters['feeType'] = $feeType->getId();
            $createFeeParameters['quantity'] = $quantity;

            $command = $this->commandCreator->create(CreateFee::class, $createFeeParameters);
            $this->commandHandlerManager->handleCommand($command, false);
        }
    }
}
