<?php

/**
 * Irfo Psv Auth Fee Trait
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as FeeCreateFee;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;

/**
 * Irfo Psv Auth Fee Trait
 */
trait IrfoPsvAuthFeeTrait
{
    /**
     * Generates application fee
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function generateApplicationFee(IrfoPsvAuth $irfoPsvAuth)
    {
        $this->extraRepos[] = 'FeeType';

        // generate application fee
        if ($irfoPsvAuth->getIsFeeExemptApplication() !== 'Y') {
            return $this->createApplicationFee($irfoPsvAuth);
        } else {
            return $this->createExemptFee($irfoPsvAuth);
        }
    }

    /**
     * Creates outstanding application fee
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function createApplicationFee(IrfoPsvAuth $irfoPsvAuth)
    {
        $irfoFeeType = $this->getRepo('FeeType')->getLatestIrfoFeeType(
            $irfoPsvAuth,
            $this->getRepo()->getRefdataReference(FeeTypeEntity::FEE_TYPE_IRFOPSVAPP)
        );

        $feeAmount = (float) $irfoFeeType->getFixedValue();

        $data = [
            'irfoPsvAuth' => $irfoPsvAuth->getId(),
            'invoicedDate' => date('Y-m-d'),
            'description' => $irfoFeeType->getDescription() . ' for Auth ' . $irfoPsvAuth->getId(),
            'feeType' => $irfoFeeType->getId(),
            'amount' => $feeAmount,
            'feeStatus' => Fee::STATUS_OUTSTANDING,
        ];

        return $this->handleSideEffect(FeeCreateFee::create($data));
    }

    /**
     * Creates exempt fee for 0 amount. Marked as PAID
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function createExemptFee(IrfoPsvAuth $irfoPsvAuth)
    {
        $irfoFeeType = $this->getRepo('FeeType')->getLatestIrfoFeeType(
            $irfoPsvAuth,
            $this->getRepo()->getRefdataReference(FeeTypeEntity::FEE_TYPE_IRFOPSVAPP)
        );

        $data = [
            'irfoPsvAuth' => $irfoPsvAuth->getId(),
            'invoicedDate' => date('Y-m-d'),
            'description' => $irfoFeeType->getDescription() . ' for Auth ' . $irfoPsvAuth->getId(),
            'feeType' => $irfoFeeType->getId(),
            'amount' => 0,
            'feeStatus' => Fee::STATUS_PAID,
            'irfoFeeExempt' => 'Y',
            'waiveReason' => $irfoPsvAuth->getExemptionDetails(),
        ];

        return $this->handleSideEffect(FeeCreateFee::create($data));
    }
}
