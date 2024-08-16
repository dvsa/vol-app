<?php

namespace Dvsa\Olcs\Api\Service;

use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Fees Helper Service
 *
 * NOTE: Does calculations as integers/pence wherever possible in order to avoid
 * floating point rounding errors.
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeesHelperService implements FactoryInterface
{
    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\Application
     */
    protected $applicationRepo;

    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\IrhpApplication
     */
    protected $irhpApplicationRepo;

    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\Fee
     */
    protected $feeRepo;

    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\FeeType
     */
    protected $feeTypeRepo;

    /**
     * Get fees pertaining to an application
     *
     * AC specify we should only get the *latest* application and interim
     * fees in the event there are multiple fees outstanding.
     *
     * @param int  $applicationId       Application Id
     * @param bool $shouldIgnoreInterim should ignore interim fee
     *
     * @return array
     */
    public function getOutstandingFeesForApplication($applicationId, $shouldIgnoreInterimFee = false)
    {
        $outstandingFees = [];

        $application = $this->applicationRepo->fetchById($applicationId);

        // get application fee
        $applicationFee = $application->getLatestOutstandingApplicationFee();
        if (!empty($applicationFee)) {
            $outstandingFees[] = $applicationFee;
        }

        // get interim fee if applicable
        if ($application->isGoods() && !$shouldIgnoreInterimFee) {
            $interimFee = $application->getLatestOutstandingInterimFee();
            if (!empty($interimFee)) {
                $outstandingFees[] = $interimFee;
            }
        }

        return $outstandingFees;
    }

    /**
     * Get fees pertaining to an irhp application
     *
     * @param int $irhpApplicationId irhp application id
     *
     * @return array
     */
    public function getOutstandingFeesForIrhpApplication(int $irhpApplicationId): array
    {
        /** @var IrhpApplication $irhpApplication */
        $irhpApplication = $this->irhpApplicationRepo->fetchById($irhpApplicationId);

        return $irhpApplication->getOutstandingFees();
    }

    /**
     * Get total outstanding fee amount for application
     *
     * @param int $applicationId application id
     *
     * @return string
     */
    public function getTotalOutstandingFeeAmountForApplication($applicationId)
    {
        $outstandingFees = $this->getOutstandingFeesForApplication($applicationId);

        $total = 0;

        if (is_array($outstandingFees)) {
            foreach ($outstandingFees as $fee) {
                $total += $fee->getOutstandingAmount();
            }
        }

        return number_format($total, 2, '.', null);
    }

    /**
     * Gets the minimum allowable payment amount for an array of fees,
     * this prevents creating invalid payment attempts which would result
     * in a zero allocated amount
     *
     * @param array $fees Fees
     *
     * @return string formatted amount
     */
    public function getMinPaymentForFees(array $fees)
    {
        $fees = $this->sortFeesByInvoiceDate($fees);

        // min. payment must be greater than the outstanding amount for all but the last fee
        $minPayment = 1; // 1 penny
        for ($i = 0; $i < count($fees) - 1; $i++) {
            $minPayment += FeeEntity::amountToPence($fees[$i]->getOutstandingAmount());
        }

        return FeeEntity::amountToPounds($minPayment);
    }

    /**
     * Gets the total outstanding payment amount for an array of fees
     *
     * @param array $fees List of Fees
     *
     * @return string formatted amount
     */
    public function getTotalOutstanding(array $fees)
    {
        $total = 0;

        foreach ($fees as $fee) {
            $total += FeeEntity::amountToPence($fee->getOutstandingAmount());
        }

        return FeeEntity::amountToPounds($total);
    }

    /**
     * Determine how a payment should be allocated to an array of fees.
     * Payment is allocated to earliest fees first (by invoicedDate)
     *
     * Zero allocations are not returned
     *
     * @param string $amount payment amount
     * @param array  $fees   array of FeeEntity
     *
     * @return array ['feeId' => 'allocatedAmount'] e.g.
     * [
     *     97 => '12.34',
     *     98 => '50.00',
     * ]
     */
    public function allocatePayments($amount, array $fees)
    {
        $fees = $this->sortFeesByInvoiceDate($fees);

        $allocations = [];

        $remaining = FeeEntity::amountToPence($amount);

        foreach ($fees as $fee) {
            if ($fee->isCancelled()) {
                continue;
            }

            $allocated = 0;
            $outstanding = FeeEntity::amountToPence($fee->getOutstandingAmount());

            if ($remaining >= $outstanding) {
                // if we have enough to pay the fee in full, allocate full amount
                $allocated = $outstanding;
            } elseif ($remaining > 0) {
                // otherwise allocate remaining available amount
                $allocated = $remaining;
            }

            // then decrement remaining available
            $remaining = ($remaining - $allocated);

            if ($allocated > 0) {
                $allocations[$fee->getId()] = FeeEntity::amountToPounds($allocated);
            }
        }

        return $allocations;
    }

    /**
     * Sort Fees
     *
     * @param array $fees List of fees
     *
     * @return array
     */
    public function sortFeesByInvoiceDate(array $fees)
    {
        $sorted = $fees;
        // sort fees in invoicedDate order
        usort(
            $sorted,
            function (FeeEntity $a, FeeEntity $b) {
                $aDateTs = $a->getInvoicedDateTime()->getTimestamp();
                $bDateTs = $b->getInvoicedDateTime()->getTimestamp();
                if ($aDateTs === $bDateTs) {
                    // if invoicedDate the same, use id as a tie-break
                    return $a->getId() < $b->getId() ? -1 : 1;
                }
                return $aDateTs < $bDateTs ? -1 : 1;
            }
        );

        return $sorted;
    }

    /**
     * Calculate amount of any overpayment. Note, will return a negative value
     * for an underpayment, although not really expected to be used as such
     *
     * @param string $receivedAmount payment amount
     * @param array  $fees           array of FeeEntity
     *
     * @return string formatted amount
     */
    public function getOverpaymentAmount($receivedAmount, $fees)
    {
        $receivedAmount = FeeEntity::amountToPence($receivedAmount);
        $outstanding = FeeEntity::amountToPence($this->getTotalOutstanding($fees));

        $overpayment = $receivedAmount - $outstanding;

        return FeeEntity::amountToPounds($overpayment);
    }

    /**
     * Get Ids From Fee
     *
     * @param FeeEntity $existingFee Fee
     *
     * @return array
     */
    public function getIdsFromFee($existingFee)
    {
        $licenceId = null;
        if ($existingFee->getLicence()) {
            $licenceId = $existingFee->getLicence()->getId();
        }

        $applicationId = null;
        if ($existingFee->getApplication()) {
            $applicationId = $existingFee->getApplication()->getId();
        }

        $busRegId = null;
        if ($existingFee->getBusReg()) {
            $busRegId = $existingFee->getBusReg()->getId();
        }

        $irfoGvPermitId = null;
        if ($existingFee->getIrfoGvPermit()) {
            $irfoGvPermitId = $existingFee->getIrfoGvPermit()->getId();
        }

        $irfoPsvAuthId = null;
        if ($existingFee->getIrfoPsvAuth()) {
            $irfoPsvAuthId = $existingFee->getIrfoPsvAuth()->getId();
        }

        return [
            'licence'      => $licenceId,
            'application'  => $applicationId,
            'busReg'       => $busRegId,
            'irfoGvPermit' => $irfoGvPermitId,
            'irfoPsvAuth'  => $irfoPsvAuthId,
        ];
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $repoManager = $container->get('RepositoryServiceManager');
        // inject required repos
        $this->applicationRepo = $repoManager->get('Application');
        $this->irhpApplicationRepo = $repoManager->get('IrhpApplication');
        $this->feeRepo = $repoManager->get('Fee');
        $this->feeTypeRepo = $repoManager->get('FeeType');
        return $this;
    }
}
