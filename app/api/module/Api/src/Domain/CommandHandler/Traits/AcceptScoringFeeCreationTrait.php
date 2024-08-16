<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Fee\Fee;

/**
 * Accept scoring fee creation trait
 */
trait AcceptScoringFeeCreationTrait
{
    /**
     * Get issue fee creation command for an application
     *
     * @param int $permitsWanted
     * @return CreateFee
     */
    public function getCreateIssueFeeCommand(IrhpApplication $irhpApplication, $permitsWanted)
    {
        $productReference = $irhpApplication->getIssueFeeProductReference();

        $feeType = $this->getRepo('FeeType')->getLatestByProductReference($productReference);

        $feeDescription = sprintf(
            '%s - %d permits',
            $feeType->getDescription(),
            $permitsWanted
        );

        return CreateFee::create(
            [
                'licence' => $irhpApplication->getLicence()->getId(),
                'irhpApplication' => $irhpApplication->getId(),
                'invoicedDate' => date('Y-m-d'),
                'description' => $feeDescription,
                'feeType' => $feeType->getId(),
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'amount' => $feeType->getFixedValue() * $permitsWanted
            ]
        );
    }
}
