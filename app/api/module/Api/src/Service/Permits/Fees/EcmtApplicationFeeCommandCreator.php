<?php

namespace Dvsa\Olcs\Api\Service\Permits\Fees;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;

class EcmtApplicationFeeCommandCreator
{
    /**
     * Create service instance
     *
     *
     * @return CreateFee
     */
    public function __construct(private readonly FeeTypeRepository $feeTypeRepo, private readonly CommandCreator $commandCreator, private readonly CurrentDateTimeFactory $currentDateTimeFactory)
    {
    }

    /**
     * Return a CreateFee command representing an application fee for the specified number of permits
     *
     * @param int $permitsRequired
     * @return CreateFee
     */
    public function create(IrhpApplicationEntity $irhpApplication, $permitsRequired)
    {
        $feeType = $this->feeTypeRepo->getLatestByProductReference(
            $irhpApplication->getApplicationFeeProductReference()
        );

        $feeDescription = sprintf(
            '%s - %d permits',
            $feeType->getDescription(),
            $permitsRequired
        );

        $currentDateTime = $this->currentDateTimeFactory->create();

        return $this->commandCreator->create(
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
    }
}
