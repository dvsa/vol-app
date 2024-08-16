<?php

/**
 * Update IrfoPsvAuth
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as FeeCreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Grant IrfoPsvAuth
 */
final class GrantIrfoPsvAuth extends AbstractCommandHandler implements TransactionedInterface
{
    use IrfoPsvAuthUpdateTrait;

    protected $repoServiceName = 'IrfoPsvAuth';

    protected $extraRepos = ['Fee', 'FeeType'];

    /**
     * Handle Grant command
     *
     * @param CommandInterface $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        // common IRFO PSV Auth update
        $irfoPsvAuth = $this->updateIrfoPsvAuth($command);

        $irfoPsvAuth->grant(
            $this->getRepo()->getRefdataReference(IrfoPsvAuth::STATUS_GRANTED),
            $this->getRepo('Fee')->fetchApplicationFeeByPsvAuthId($irfoPsvAuth->getId())
        );

        $this->getRepo()->save($irfoPsvAuth);

        $result = new Result();
        $result->addId('irfoPsvAuth', $irfoPsvAuth->getId());
        $result->addMessage('IRFO PSV Auth granted successfully');

        // create application and copies fee
        $result->merge($this->generateAnnualFee($irfoPsvAuth));
        $result->merge($this->createCopiesFee($irfoPsvAuth));

        return $result;
    }

    /**
     * Determine if Annual Fee should be exempt or not
     *
     * @return Result
     */
    protected function generateAnnualFee(IrfoPsvAuth $irfoPsvAuth)
    {
        if ($irfoPsvAuth->getIsFeeExemptAnnual() === 'Y') {
            return $this->createExemptAnnualFee($irfoPsvAuth);
        } else {
            return $this->createOutstandingAnnualFee($irfoPsvAuth);
        }
    }

    /**
     * Creates Annual fee
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\NotFoundException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function createOutstandingAnnualFee(IrfoPsvAuth $irfoPsvAuth)
    {
        $irfoFeeType = $this->getRepo('FeeType')->getLatestIrfoFeeType(
            $irfoPsvAuth,
            $this->getRepo()->getRefdataReference(FeeTypeEntity::FEE_TYPE_IRFOPSVANN)
        );

        $feeAmount = (float) ($irfoPsvAuth->getValidityPeriod() *  $irfoFeeType->getFixedValue());

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
     * Creates Annual exempt fee
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\NotFoundException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function createExemptAnnualFee(IrfoPsvAuth $irfoPsvAuth)
    {
        $irfoFeeType = $this->getRepo('FeeType')->getLatestIrfoFeeType(
            $irfoPsvAuth,
            $this->getRepo()->getRefdataReference(FeeTypeEntity::FEE_TYPE_IRFOPSVANN)
        );

        $data = [
            'irfoPsvAuth' => $irfoPsvAuth->getId(),
            'invoicedDate' => date('Y-m-d'),
            'description' => $irfoFeeType->getDescription() . ' for Auth ' . $irfoPsvAuth->getId(),
            'feeType' => $irfoFeeType->getId(),
            'amount' => 0,
            'feeStatus' => Fee::STATUS_PAID,
        ];

        return $this->handleSideEffect(FeeCreateFee::create($data));
    }

    /**
     * Create the copies fee, based on copies requrired
     *
     * @return Result
     */
    private function createCopiesFee(IrfoPsvAuth $irfoPsvAuth)
    {
        $irfoFeeType = $this->getRepo('FeeType')->getLatestIrfoFeeType(
            $irfoPsvAuth,
            $this->getRepo()->getRefdataReference(FeeTypeEntity::FEE_TYPE_IRFOPSVCOPY)
        );

        $feeAmount = (float) $irfoFeeType->getFixedValue() * $irfoPsvAuth->getCopiesRequired();

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
}
