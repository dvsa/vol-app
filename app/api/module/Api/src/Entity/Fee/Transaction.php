<?php

namespace Dvsa\Olcs\Api\Entity\Fee;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * Transaction Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="txn",
 *    indexes={
 *        @ORM\Index(name="ix_txn_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_txn_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_txn_status", columns={"status"}),
 *        @ORM\Index(name="ix_txn_waive_recommender_user_id", columns={"waive_recommender_user_id"}),
 *        @ORM\Index(name="ix_txn_processed_by_user_id", columns={"processed_by_user_id"}),
 *        @ORM\Index(name="ix_txn_olbs_key", columns={"olbs_key"}),
 *        @ORM\Index(name="ix_txn_payment_method", columns={"payment_method"}),
 *        @ORM\Index(name="ix_txn_type", columns={"type"}),
 *        @ORM\Index(name="ix_txn_reference", columns={"reference"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_txn_receipt_document_id", columns={"receipt_document_id"})
 *    }
 * )
 */
class Transaction extends AbstractTransaction implements OrganisationProviderInterface
{
    public const STATUS_OUTSTANDING = 'pay_s_os';
    public const STATUS_CANCELLED = 'pay_s_cn';
    public const STATUS_FAILED = 'pay_s_fail';
    // 'paid' and 'complete' are synonymous
    public const STATUS_PAID = 'pay_s_pd';
    public const STATUS_COMPLETE = 'pay_s_pd';

    public const TYPE_WAIVE = 'trt_waive';
    public const TYPE_PAYMENT = 'trt_payment';
    public const TYPE_REFUND = 'trt_refund';
    public const TYPE_REVERSAL = 'trt_reversal';
    public const TYPE_ADJUSTMENT = 'trt_other';

    public const CURRENCY_SYMBOL = '£';

    /**
     * Is Outstanding
     *
     * @return boolean
     */
    public function isOutstanding()
    {
        return $this->getStatus()->getId() === self::STATUS_OUTSTANDING;
    }

    /**
     * Is Paid
     *
     * @return boolean
     * @deprecated
     */
    public function isPaid()
    {
        return $this->getStatus()->getId() === self::STATUS_PAID;
    }

    /**
     * Is Complete
     *
     * @return boolean
     */
    public function isComplete()
    {
        return $this->getStatus()->getId() === self::STATUS_COMPLETE;
    }

    /**
     * Gets the NET amount of any positive/negative feeTransactions
     *
     * @param bool $absolute Take only absolute value
     *
     * @return string
     */
    public function getTotalAmount($absolute = false)
    {
        $total = 0;

        $this->getFeeTransactions()->forAll(
            function ($key, $ft) use (&$total) {
                /** @var FeeTransaction $ft */
                $total += Fee::amountToPence($ft->getAmount());
                return true;
            }
        );

        if ($absolute) {
            $total = abs($total);
        }

        return Fee::amountToPounds($total);
    }

    /**
     * Get Calculated Bundle Values
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'amount' => $this->getTotalAmount(),
            'displayReversalOption' => $this->displayReversalOption(),
            'displayAdjustmentOption' => $this->displayAdjustmentOption(),
            'canReverse' => $this->canReverse(),
            'canAdjust' => $this->canAdjust(),
            'displayAmount' => $this->getDisplayAmount(),
            'amountAfterAdjustment' => $this->getAmountAfterAdjustment(),
        ];
    }

    /**
     * Work out the amount prior to adjustment by summing the reversed
     * feeTransaction amounts
     *
     * @return string
     */
    public function getAmountBeforeAdjustment()
    {
        $total = 0;

        $this->getFeeTransactions()->forAll(
            function ($key, $ft) use (&$total) {
                /** @var FeeTransaction $ft */
                if ($ft->getReversedFeeTransaction()) {
                    $total += Fee::amountToPence($ft->getAmount());
                }
                return true;
            }
        );

        return Fee::amountToPounds($total * -1);
    }

    /**
     * Work out the amount after adjustment by summing the positive
     * feeTransaction amounts
     *
     * @return string
     */
    public function getAmountAfterAdjustment()
    {
        $total = 0;

        $this->getFeeTransactions()->forAll(
            function ($key, $ft) use (&$total) {
                /** @var FeeTransaction $ft */
                if (is_null($ft->getReversedFeeTransaction())) {
                    $total += Fee::amountToPence($ft->getAmount());
                }
                return true;
            }
        );

        return Fee::amountToPounds($total);
    }

    /**
     * Small helper to get array of feeTransaction IDs, useful for logging
     *
     * @return array
     */
    public function getFeeTransactionIds()
    {
        $ftIds = [];

        if (!empty($this->getFeeTransactions())) {
            $ftIds = array_map(
                fn($ft) =>
                    /** @var FeeTransaction $ft */
                    $ft->getId(),
                $this->getFeeTransactions()->toArray()
            );
        }

        return $ftIds;
    }

    /**
     * Is Waive
     *
     * @return boolean
     */
    public function isWaive()
    {
        return $this->getType()->getId() === self::TYPE_WAIVE;
    }

    /**
     * Is Payment
     *
     * @return boolean
     */
    public function isPayment()
    {
        return $this->getType()->getId() === self::TYPE_PAYMENT;
    }

    /**
     * Is Adjustment
     *
     * @return boolean
     */
    public function isAdjustment()
    {
        return $this->getType()->getId() === self::TYPE_ADJUSTMENT;
    }

    /**
     * Is Reversal
     *
     * @return boolean
     */
    public function isReversal()
    {
        return $this->getType()->getId() === self::TYPE_REVERSAL;
    }

    /**
     * Is Card
     *
     * @return boolean
     */
    public function isCard()
    {
        return in_array(
            $this->getPaymentMethod()->getId(),
            [
                Fee::METHOD_CARD_ONLINE,
                Fee::METHOD_CARD_OFFLINE,
            ],
            true
        );
    }

    /**
     * Returns Fee Transactions For Reversal
     *
     * @return array
     */
    public function getFeeTransactionsForReversal()
    {
        $feeTransactions = [];

        foreach ($this->getFeeTransactions() as $ft) {
            if (empty($ft->getReversedFeeTransaction()) && !$ft->isRefundedOrReversed()) {
                // only return feeTransactions that aren't reversed or reversing
                $feeTransactions[] = $ft;
            }
        }

        return $feeTransactions;
    }

    /**
     * Returns Fee Transactions For Adjustment
     *
     * @return array
     */
    public function getFeeTransactionsForAdjustment()
    {
        $feeTransactions = [];

        $this->getFeeTransactions()->forAll(
            function ($key, $ft) use (&$feeTransactions) {
                /** @var FeeTransaction $ft */
                if (is_null($ft->getReversedFeeTransaction())) {
                    $feeTransactions[] = $ft;
                }
                return true;
            }
        );

        return $feeTransactions;
    }

    /**
     * Determine whether to show the 'Reverse' option for a transaction
     *
     * @see canReverse()
     * Note: there are additional checks for whether a transaction can
     * ultimately be reversed
     *
     * @return bool
     */
    public function displayReversalOption()
    {
        if ($this->isMigrated()) {
            return false;
        }

        return $this->isCompletePaymentOrAdjustment();
    }

    /**
     * This is a common check when doing refunds/reversals/adjustments
     *
     * @return  boolean
     */
    public function isCompletePaymentOrAdjustment()
    {
        return (($this->isPayment() || $this->isAdjustment()) && $this->isComplete());
    }

    /**
     * Determine whether a transaction is reversable, i.e.
     * none of the allocated fee amounts has already been refunded or reversed
     *
     * @return bool
     */
    public function canReverse()
    {
        // reuse displayReversalOption logic for initial checks
        if (!$this->displayReversalOption()) {
            return false;
        }

        return !$this->isReversed();
    }

    /**
     * Determine whether to show the 'Adjust' option for a transaction
     *
     * @see canAdjust()
     *
     * @return bool
     */
    public function displayAdjustmentOption()
    {
        return $this->canAdjust();
    }

    /**
     * Determine whether a transaction can be adjusted. Can only adjust payments
     * or adjustments that have not previously been reversed (adjustment counts
     * as a reversal in terms of data recorded)
     *
     * @return bool
     */
    public function canAdjust()
    {
        return (
            ($this->isPayment() || $this->isAdjustment())
            && !$this->isCard()
            && !$this->isReversed()
        );
    }

    /**
     * Is Reversed
     *
     * @return bool
     */
    public function isReversed()
    {
        foreach ($this->getFeeTransactions() as $ft) {
            if ($ft->isRefundedOrReversed()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Processed By Full Name
     *
     * @return string|null
     */
    public function getProcessedByFullName()
    {
        $processedByUser = $this->getProcessedByUser();
        if (
            $processedByUser !== null
            && $processedByUser->getContactDetails() !== null
            && $processedByUser->getContactDetails()->getPerson() !== null
        ) {
            return $this->getProcessedByUser()->getContactDetails()->getPerson()->getFullName();
        } elseif ($processedByUser !== null) {
            return $processedByUser->getLoginId();
        }

        return null;
    }

    /**
     * Get all fees associated to the transaction, via the feeTransactions
     *
     * @return Fee[]
     */
    public function getFees()
    {
        $fees = [];
        foreach ($this->getFeeTransactions() as $ft) {
            $fees[$ft->getFee()->getId()] = $ft->getFee();
        }

        return $fees;
    }

    /**
     * Gets the 'display' amount for the transaction. e.g.
     *
     * @return string e.g. '£12.34' or '£12.34 to £23.45' for an adjustment
     */
    public function getDisplayAmount()
    {
        if ($this->isAdjustment()) {
            return sprintf(
                '%1$s%2$s to %1$s%3$s',
                self::CURRENCY_SYMBOL,
                $this->getAmountBeforeAdjustment(),
                $this->getAmountAfterAdjustment()
            );
        }

        return self::CURRENCY_SYMBOL . $this->getTotalAmount(true);
    }

    /**
     * Get the previous transaction (for a reversal or adjustment)
     *
     * @return Transaction|null
     */
    public function getPreviousTransaction()
    {
        $transaction = null;
        if ($this->isAdjustment() || $this->isReversal()) {
            $this->getFeeTransactions()->forAll(
                function ($key, $ft) use (&$transaction) {
                    /** @var FeeTransaction $ft */
                    if ($ft->getReversedFeeTransaction()) {
                        $transaction = $ft->getReversedFeeTransaction()->getTransaction();
                        return false;
                    }
                    return true;
                }
            );
        }

        return $transaction;
    }

    /**
     * Get Amount Allocated for specified FeeId
     *
     * @param int $feeId Fee Id
     *
     * @return string formatted amount
     */
    public function getAmountAllocatedToFeeId($feeId)
    {
        $amount = null;

        $this->getFeeTransactions()->forAll(
            function ($key, $feeTransaction) use ($feeId, &$amount) {
                /** @var FeeTransaction $feeTransaction */
                if ($feeTransaction->getFee()->getId() == $feeId && !$feeTransaction->getReversedFeeTransaction()) {
                    $amount = $feeTransaction->getAmount();
                    return false;
                }
                return true;
            }
        );

        return $amount;
    }

    /**
     * Is Mirgated
     *
     * @return bool
     */
    public function isMigrated()
    {
        if (
            $this->getPaymentMethod() !== null
            && in_array($this->getPaymentMethod()->getId(), [Fee::METHOD_RECEIPT, Fee::METHOD_MIGRATED], true)
        ) {
            return true;
        }

        if ($this->getLegacyStatus() !== null) {
            return true;
        }

        return false;
    }

    /**
     * Get organisations this entity is linked to
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation[]
     */
    public function getRelatedOrganisation()
    {
        $organisations = [];
        foreach ($this->getFees() as $feeId => $fee) {
            $organisations[$fee->getRelatedOrganisation()->getId()] = $fee->getRelatedOrganisation();
        }

        return $organisations;
    }
}
