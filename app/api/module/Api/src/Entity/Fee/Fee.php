<?php

namespace Dvsa\Olcs\Api\Entity\Fee;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * Fee Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="fee",
 *    indexes={
 *        @ORM\Index(name="ix_fee_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_fee_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_fee_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_fee_task_id", columns={"task_id"}),
 *        @ORM\Index(name="ix_fee_fee_type_id", columns={"fee_type_id"}),
 *        @ORM\Index(name="ix_fee_parent_fee_id", columns={"parent_fee_id"}),
 *        @ORM\Index(name="ix_fee_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_fee_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_fee_irfo_gv_permit_id", columns={"irfo_gv_permit_id"}),
 *        @ORM\Index(name="ix_fee_irfo_psv_auth_id", columns={"irfo_psv_auth_id"}),
 *        @ORM\Index(name="ix_fee_fee_status", columns={"fee_status"})
 *    }
 * )
 */
class Fee extends AbstractFee implements OrganisationProviderInterface
{
    public const STATUS_OUTSTANDING       = 'lfs_ot';
    public const STATUS_PAID              = 'lfs_pd';
    public const STATUS_CANCELLED         = 'lfs_cn';
    public const STATUS_REFUND_PENDING    = 'lfs_refund_pending';
    public const STATUS_REFUND_FAILED     = 'lfs_refund_failed';
    public const STATUS_REFUNDED          = 'lfs_refunded';

    public const ACCRUAL_RULE_LICENCE_START            = 'acr_licence_start';
    public const ACCRUAL_RULE_CONTINUATION             = 'acr_continuation';
    public const ACCRUAL_RULE_IMMEDIATE                = 'acr_immediate';
    public const ACCRUAL_RULE_IRHP_PERMIT_3_MONTHS     = 'acr_irhp_permit_3_months';
    public const ACCRUAL_RULE_IRHP_PERMIT_6_MONTHS     = 'acr_irhp_permit_6_months';
    public const ACCRUAL_RULE_IRHP_PERMIT_9_MONTHS     = 'acr_irhp_permit_9_months';
    public const ACCRUAL_RULE_IRHP_PERMIT_12_MONTHS    = 'acr_irhp_permit_12_months';

    public const METHOD_CARD_ONLINE  = 'fpm_card_online';
    public const METHOD_CARD_OFFLINE = 'fpm_card_offline';
    public const METHOD_CASH         = 'fpm_cash';
    public const METHOD_CHEQUE       = 'fpm_cheque';
    public const METHOD_POSTAL_ORDER = 'fpm_po';
    public const METHOD_WAIVE        = 'fpm_waive';
    public const METHOD_REFUND       = 'fpm_refund';
    public const METHOD_REVERSAL     = 'fpm_reversal';
    public const METHOD_RECEIPT      = 'fpm_rcpt';
    public const METHOD_MIGRATED     = 'fpm_migrated';

    public const DEFAULT_INVOICE_ADDRESS_LINE = 'Miscellaneous payment';
    // CPMS enforces 'valid' postcodes :(
    public const DEFAULT_POSTCODE = 'LS9 6NF';

    /** @var int */
    private $daysToPayIssueFee;

    public function __construct(FeeType $feeType, $netAmount, RefData $feeStatus)
    {
        parent::__construct();

        $this->feeType = $feeType;
        $this->netAmount = $netAmount;
        $this->feeStatus = $feeStatus;

        $this->setVatAndGrossAmountsFromNetAmountUsingRate($feeType->getVatRate());
    }

    /**
     * Loop through a fee's payment records and check if any are outstanding
     */
    public function hasOutstandingPayment()
    {
        foreach ($this->getFeeTransactions() as $fp) {
            if ($fp->getTransaction()->isOutstanding()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Loop through a fee's payment records and check if any are outstanding that are not waives
     *
     * @param int $paymentPaymentsTimeout pending payments timeout
     *
     * @return void
     */
    public function hasOutstandingPaymentExcludeWaive($pendingPaymentsTimeout)
    {
        foreach ($this->getFeeTransactions() as $fp) {
            /** @var Transaction $transaction */
            $transaction = $fp->getTransaction();
            $transactionDate = $this->asDateTime($transaction->getCreatedOn());
            $interval = new \DateInterval('PT' . $pendingPaymentsTimeout . 'S');
            $maxPendingDate = $transactionDate->add($interval);
            if (
                $transaction->isOutstanding()
                && $transaction->getType()->getId() !== Transaction::TYPE_WAIVE
                && $maxPendingDate > (new DateTime('now'))
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine 'rule start date' for a fee
     *
     * @see https://jira.i-env.net/browse/OLCS-6005 for business rules
     *
     * @return DateTime|null
     */
    public function getRuleStartDate()
    {
        $rule = $this->getFeeType()->getAccrualRule()->getId();
        switch ($rule) {
            case self::ACCRUAL_RULE_IMMEDIATE:
                return new DateTime();
            case self::ACCRUAL_RULE_LICENCE_START:
                $licenceStart = $this->getLicence()->getInForceDate();
                if (!is_null($licenceStart)) {
                    return new \DateTime($licenceStart);
                }
                break;
            case self::ACCRUAL_RULE_CONTINUATION:
                $licenceExpiry = $this->getLicence()->getExpiryDate();
                if (is_null($licenceExpiry)) {
                    break;
                }
                $date = new \DateTime($licenceExpiry);
                if ($date->diff(new \DateTime())->y >= 4) {
                    $date->sub(new \DateInterval('P5Y'));
                }
                $date->add(new \DateInterval('P1D'));
                return $date;
            case self::ACCRUAL_RULE_IRHP_PERMIT_3_MONTHS:
            case self::ACCRUAL_RULE_IRHP_PERMIT_6_MONTHS:
            case self::ACCRUAL_RULE_IRHP_PERMIT_9_MONTHS:
            case self::ACCRUAL_RULE_IRHP_PERMIT_12_MONTHS:
                $application = $this->getIrhpApplication();

                $irhpPermitApplication = $application->getIrhpPermitApplications()->first();
                if ($irhpPermitApplication) {
                    return $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock()->getValidFrom(true);
                }
                break;
            default:
                break;
        }
    }

    /**
     * Returns true if the accrual rule for this fee type can be earlier than the fee invoice date
     *
     * @return bool
     */
    public function isAccrualBeforeInvoiceDatePermitted()
    {
        $rule = $this->getFeeType()->getAccrualRule()->getId();
        return in_array(
            $rule,
            [
                self::ACCRUAL_RULE_IRHP_PERMIT_3_MONTHS,
                self::ACCRUAL_RULE_IRHP_PERMIT_6_MONTHS,
                self::ACCRUAL_RULE_IRHP_PERMIT_9_MONTHS,
                self::ACCRUAL_RULE_IRHP_PERMIT_12_MONTHS,
            ]
        );
    }

    /**
     * Is the Accrual rule date before the invoice date
     *
     * @return boolean
     */
    public function isRuleBeforeInvoiceDate()
    {
        $ruleDate = $this->getRuleStartDate();
        if ($ruleDate instanceof \DateTime && $this->getInvoicedDateTime() instanceof \DateTime) {
            $diff = $this->getInvoicedDateTime()->diff($ruleDate);
            return $diff->invert === 1;
        }

        return false;
    }

    public function getDefermentPeriod()
    {
        $map = [
            self::ACCRUAL_RULE_LICENCE_START            => 60,
            self::ACCRUAL_RULE_CONTINUATION             => 60,
            self::ACCRUAL_RULE_IMMEDIATE                => 1,
            self::ACCRUAL_RULE_IRHP_PERMIT_3_MONTHS     => 3,
            self::ACCRUAL_RULE_IRHP_PERMIT_6_MONTHS     => 6,
            self::ACCRUAL_RULE_IRHP_PERMIT_9_MONTHS     => 9,
            self::ACCRUAL_RULE_IRHP_PERMIT_12_MONTHS    => 12,
        ];

        $rule = $this->getFeeType()->getAccrualRule()->getId();

        if (array_key_exists($rule, $map)) {
            return $map[$rule];
        }
    }

    public function allowEdit()
    {
        return !in_array(
            $this->getFeeStatus()->getId(),
            [
                self::STATUS_PAID,
                self::STATUS_CANCELLED,
            ]
        );
    }

    public function getPaymentMethod()
    {
        $transaction = $this->getLatestTransaction();
        if ($transaction) {
            return $transaction->getPaymentMethod();
        }
    }

    public function getProcessedBy()
    {
        $transaction = $this->getLatestTransaction();
        if ($transaction) {
            $user = $transaction->getProcessedByUser();
            if ($user) {
                return $user->getLoginId();
            }
        }
    }

    public function getPayer()
    {
        $transaction = $this->getLatestTransaction();
        if ($transaction) {
            return $transaction->getPayerName();
        }
    }

    public function getSlipNo()
    {
        $transaction = $this->getLatestTransaction();
        if ($transaction) {
            return $transaction->getPayingInSlipNumber();
        }
    }

    public function getChequePoNumber()
    {
        $transaction = $this->getLatestTransaction();
        if ($transaction) {
            return $transaction->getChequePoNumber();
        }
    }

    public function getWaiveReason()
    {
        $transaction = $this->getOutstandingWaiveTransaction();
        if ($transaction) {
            return $transaction->getComment();
        }
    }

    /**
     * @return Transaction
     */
    public function getOutstandingWaiveTransaction()
    {
        $feeTransactions = $this->getFeeTransactions();

        if (empty($feeTransactions) || $feeTransactions->isEmpty()) {
            return;
        }

        $ft = $feeTransactions->filter(
            function ($ft) {
                $transaction = $ft->getTransaction();
                return (
                    $transaction->getType()->getId() === Transaction::TYPE_WAIVE
                    &&
                    $transaction->isOutstanding()
                );
            }
        )->first(); // there should only ever be one!

        return $ft ? $ft->getTransaction() : null;
    }

    /**
     * @return string e.g. '1234.56'
     */
    public function getOutstandingAmount()
    {
        $amount = self::amountToPence($this->getGrossAmount());

        $ftSum = 0;
        $this->getFeeTransactions()->forAll(
            function ($key, $feeTransaction) use (&$ftSum) {
                if ($feeTransaction->getTransaction()->isComplete()) {
                    $ftSum += self::amountToPence($feeTransaction->getAmount());
                }
                return true;
            }
        );

        return self::amountToPounds($amount - $ftSum);
    }

    /**
     * @return string|null
     */
    public function getLatestPaymentRef()
    {
        $transaction = $this->getLatestTransaction();
        if ($transaction) {
            return $transaction->getReference();
        }
    }

    /**
     * @return FeeTransaction|void
     */
    protected function getLatestFeeTransaction()
    {
        // Criteria won't handle relations, only properties, so get them all and
        // sort the transactions manually
        if ($this->getFeeTransactions() && $this->getFeeTransactions()->count()) {
            $transactions = [];
            foreach ($this->getFeeTransactions() as $key => $ft) {
                if ($ft->getTransaction()->isComplete()) {
                    $transactions[$key] = $ft->getTransaction();
                }
            }
            uasort(
                $transactions,
                function ($a, $b) {
                    if ($a->getCompletedDate() == $b->getCompletedDate()) {
                        return ($b->getCreatedOn() <=> $a->getCreatedOn());
                    }
                    return ($b->getCompletedDate() <=> $a->getCompletedDate());
                }
            );

            if (!empty($transactions)) {
                $key = array_keys($transactions)[0];
                return $this->getFeeTransactions()->get($key);
            }
        }
    }

    /**
     * Get latest transaction
     *
     * @return Transaction|void
     */
    public function getLatestTransaction()
    {
        $ft = $this->getLatestFeeTransaction();
        if ($ft) {
            return $ft->getTransaction();
        }
    }

    /**
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'outstanding' => $this->getOutstandingAmount(),
            'receiptNo' => $this->getLatestPaymentRef(),
            'amount' => $this->getGrossAmount(),
            'dueDate' => $this->getDueDate(),
            'ruleDateBeforeInvoice' => $this->isRuleBeforeInvoiceDate(),
            'isExpiredForLicence' => $this->isExpiredForLicence(),
            'isOutstanding' => $this->isOutstanding(),
            'isEcmtIssuingFee' => $this->isEcmtIssuingFee(),
            'isAccrualBeforeInvoiceDatePermitted' => $this->isAccrualBeforeInvoiceDatePermitted()
        ];
    }

    /**
     * Is this fee attached to the expired licence
     *
     * @return bool
     */
    public function isExpiredForLicence()
    {
        $licence = $this->getLicence();
        if ($licence !== null && $licence->getExpiryDate() !== null) {
            $today = (new DateTime())->setTime(0, 0, 0)->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            if ($licence->getExpiryDateAsDate() < $today) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Organisation|null
     */
    public function getOrganisation()
    {
        if (!empty($this->getLicence())) {
            return $this->getLicence()->getOrganisation();
        }

        if (!empty($this->getIrfoGvPermit())) {
            return $this->getIrfoGvPermit()->getOrganisation();
        }

        if (!empty($this->getIrfoPsvAuth())) {
            return $this->getIrfoPsvAuth()->getOrganisation();
        }
    }

    /**
     * Get customer name for invoice
     *
     * @return string
     */
    public function getCustomerNameForInvoice()
    {
        $name = null;

        if (!empty($this->getOrganisation())) {
            $name = $this->getOrganisation()->getName();
        }

        return $name;
    }

    /**
     * Get customer address for invoice
     *
     * @return Address|null
     */
    public function getCustomerAddressForInvoice()
    {
        if (!empty($this->getLicence())) {
            $contactDetails = $this->getLicence()->getCorrespondenceCd();
            if (!empty($contactDetails)) {
                return $contactDetails->getAddress();
            }
        }

        $organisation = null;

        if (!empty($this->getIrfoGvPermit())) {
            $organisation = $this->getIrfoGvPermit()->getOrganisation();
        }

        if (!empty($this->getIrfoPsvAuth())) {
            $organisation = $this->getIrfoPsvAuth()->getOrganisation();
        }

        if (!empty($organisation)) {
            $contactDetails = $organisation->getIrfoContactDetails();
            if (!empty($contactDetails)) {
                return $contactDetails->getAddress();
            }
        }

        return null;
    }

    /**
     * @return boolean
     */
    public function isBalancingFee()
    {
        return $this->getFeeType()->isAdjustment();
    }

    /**
     * @return boolean
     */
    public function isNewApplicationFee()
    {
        return $this->getFeeType()->getFeeType()->getId() === FeeType::FEE_TYPE_APP;
    }

    /**
     * @return boolean
     */
    public function isVariationFee()
    {
        return $this->getFeeType()->getFeeType()->getId() === FeeType::FEE_TYPE_VAR;
    }

    /**
     * @return boolean
     */
    public function isGrantFee()
    {
        return $this->getFeeType()->getFeeType()->getId() === FeeType::FEE_TYPE_GRANT;
    }

    /**
     * Get the 'sales person reference', also known as 'cost centre reference'
     * for a fee. This is either a traffic area code or one of:
     *
     * @return string
     */
    public function getSalesPersonReference()
    {
        $costCentreRef = $this->getFeeType()->getCostCentreRef();

        if ($costCentreRef === FeeType::COST_CENTRE_REF_TYPE_LICENSING) {
            return $this->getLicence()->getTrafficArea()->getSalesPersonReference();
        }

        return $costCentreRef;
    }

    /**
     * @return bool
     */
    public function isPartPaid()
    {
        return $this->getOutstandingAmount() < $this->getGrossAmount();
    }

    /**
     * @return bool
     */
    public function isFullyOutstanding()
    {
        return $this->getFeeStatus()->getId() === self::STATUS_OUTSTANDING && !$this->isPartPaid();
    }

    /**
     * @return bool
     */
    public function isOutstanding()
    {
        return $this->getFeeStatus()->getId() === self::STATUS_OUTSTANDING;
    }

    /**
     * @return boolean
     */
    public function isCancelled()
    {
        return $this->getFeeStatus()->getId() === self::STATUS_CANCELLED;
    }

    public function isPaid()
    {
        return $this->getFeeStatus()->getId() === self::STATUS_PAID;
    }

    public function isMigrated()
    {
        /** @var FeeTransaction $feeTransaction */
        foreach ($this->getFeeTransactions() as $feeTransaction) {
            if ($feeTransaction->getTransaction()->isMigrated()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function isEcmtIssuingFee()
    {
        return $this->getFeeType()->getFeeType()->getId() === FeeType::FEE_TYPE_ECMT_ISSUE;
    }

    /**
     * @return float
     */
    public function getFeeTypeAmount()
    {
        return $this->getFeeType()->getAmount();
    }

    /**
     * Set the value of days to pay issue fee, used in the getDueDate method
     *
     * @param int $daysToPayIssueFee
     */
    public function setDaysToPayIssueFee($daysToPayIssueFee)
    {
        $this->daysToPayIssueFee = $daysToPayIssueFee;
    }

    /**
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return DateTime|string|null
     */
    public function getDueDate($asDateTime = false)
    {
        if (!isset($this->daysToPayIssueFee)) {
            // we can only calculate the due date if setDaysToPayIssueFee has been previously called
            return null;
        }

        if (!$this->isOutstanding()) {
            // don't need to calculate a due date for anything but outstanding
            return null;
        }

        if ($this->isEcmtIssuingFee()) {
            // for now we only need due date for ECMT issuing fee
            $invoicedDate = $this->getInvoicedDate(true);

            if (!isset($invoicedDate)) {
                return null;
            }

            $date = clone $invoicedDate;
            $date->add(\DateInterval::createFromDateString('+' . $this->daysToPayIssueFee . ' weekdays'));

            return ($asDateTime === true) ? $date : $date->format(DateTime::ISO8601);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function canRefund()
    {
        if ($this->isMigrated()) {
            return false;
        }

        if ($this->isCancelled()) {
            // cancelled fees are not refundable
            return false;
        }

        // can only refund if there are non-refunded payments
        foreach ($this->getFeeTransactions() as $ft) {
            if ($ft->getTransaction()->isCompletePaymentOrAdjustment() && !$ft->isRefundedOrReversed()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getFeeTransactionsForRefund()
    {
        $feeTransactions = [];

        foreach ($this->getFeeTransactions() as $ft) {
            if (
                $ft->getTransaction()->isCompletePaymentOrAdjustment()
                && !$ft->isRefundedOrReversed()
                && empty($ft->getReversedFeeTransaction())
            ) {
                $feeTransactions[] = $ft;
            }
        }

        return $feeTransactions;
    }

    /**
     * Method to encapsulate the VAT calculations. Takes a percentage rate
     * parameter and expects net amount to be already set.
     *
     * @param float $rate percentage e.g. 20 or 17.5
     */
    public function setVatAndGrossAmountsFromNetAmountUsingRate($rate)
    {
        $net = $this->getNetAmount();

        $vat = $net * $rate; // this gives value in pence
        $vat = floor($vat); // round down to nearest penny
        $vat = self::amountToPounds($vat); // convert to pounds

        $this->setVatAmount($vat);

        $this->setGrossAmount($net + $vat);
    }

    /**
     * @param string $amount e.g. '4.56'
     * @return int e.g. 456
     */
    public static function amountToPence($amount)
    {
        if ($amount === null) {
            return 0;
        }

        return (int) number_format((float) $amount, 2, '', '');
    }

    /**
     * @param int $amount e.g. 456
     * @return string e.g. '4.56'
     */
    public static function amountToPounds($amount)
    {
        return number_format($amount / 100, 2, '.', '');
    }

    /**
     * @return string formatted amount
     */
    public function getAmountAllocatedByTransactionId($transactionId)
    {
        $amount = null;

        $this->getFeeTransactions()->forAll(
            function ($key, $feeTransaction) use ($transactionId, &$amount) {
                if ($feeTransaction->getTransaction()->getId() == $transactionId) {
                    $amount = $feeTransaction->getAmount();
                    return false;
                }
                return true;
            }
        );

        return $amount;
    }

    /**
     * Get InvoicedDate as a DateTime
     *
     * @return \DateTime!null
     */
    public function getInvoicedDateTime()
    {
        if (is_string(parent::getInvoicedDate())) {
            return new \DateTime(parent::getInvoicedDate());
        }

        return parent::getInvoicedDate();
    }

    /**
     * Get organisations this entity is linked to
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation|null
     */
    public function getRelatedOrganisation()
    {
        if ($this->getApplication()) {
            return $this->getApplication()->getRelatedOrganisation();
        }

        if ($this->getBusReg()) {
            return $this->getBusReg()->getRelatedOrganisation();
        }

        if ($this->getLicence()) {
            return $this->getLicence()->getRelatedOrganisation();
        }

        if ($this->getIrfoGvPermit()) {
            return $this->getIrfoGvPermit()->getRelatedOrganisation();
        }

        if ($this->getIrfoPsvAuth()) {
            return $this->getIrfoPsvAuth()->getRelatedOrganisation();
        }

        return null;
    }

    public function getCustomerReference()
    {
        if (empty($this->getOrganisation())) {
            return null;
        }
        return $this->getOrganisation()->getId();
    }

    /**
     * Remove any existing association with an IrhpPermitApplication instance
     */
    public function removeIrhpPermitApplicationAssociation()
    {
        $this->irhpPermitApplication = null;
    }
}
