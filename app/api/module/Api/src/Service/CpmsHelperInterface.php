<?php

/**
 * Cpms Helper Interface
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Service;

use CpmsClient\Service\ApiService;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;

/**
 * Cpms Helper Interface
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
interface CpmsHelperInterface
{
    public const PAYMENT_IN_PROGRESS  = 800;
    public const PAYMENT_SUCCESS      = 801;
    public const PAYMENT_FAILURE      = 802;
    public const PAYMENT_CANCELLATION = 807;
    public const PAYMENT_REFUNDED     = 809;
    public const PAYMENT_REFUND_REQUESTED = 822;
    public const PAYMENT_GATEWAY_ERROR = 810;
    public const PAYMENT_PAYMENT_CHARGED_BACK = 820;
    public const PAYMENT_AWAITING_GATEWAY_URL = 824;
    public const PAYMENT_GATEWAY_REDIRECT_URL_RECEIVED = 825;
    public const PAYMENT_END_OF_FLOW_SIGNALLED = 826;
    public const PAYMENT_CARD_PAYMENT_CONFIRMED = 827;
    public const PAYMENT_SYSTEM_ERROR = 828;
    public const PAYMENT_ACTIVELY_BEING_TAKEN = 830;
    public const PAYMENT_ABANDONED = 834;

    public const RESPONSE_SUCCESS = '000';

    /**
     * Initiate a card payment
     *
     * @param string $redirectUrl redirect back to here from payment gateway
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function initiateCardRequest($redirectUrl, array $fees);

    /**
     * Initiate a stored card payment payment
     *
     * @param string $redirectUrl redirect back to here from payment gateway
     * @param string $storedCardReference Stored card reference
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function initiateStoredCardRequest($redirectUrl, array $fees, $storedCardReference);

    /**
     * Initiate a card not present (CNP) payment
     *
     * @param string $redirectUrl redirect back to here from payment gateway
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function initiateCnpRequest($redirectUrl, array $fees);

    /**
     * Update CPMS with payment result
     *
     * @param string    $reference payment reference / guid
     * @param array     $data      response data from the payment gateway
     * @param FeeEntity $fee       fee
     *
     * @return array|mixed response
     * @see CpmsClient\Service\ApiService::put()
     */
    public function handleResponse($reference, $data, $fee);

    /**
     * Determine the status of a payment/transaction
     *
     * @param string    $receiptReference receipt reference
     * @param FeeEntity $fee              fee
     *
     * @return array ['code' => payment status code, 'message' => 'CPMS error message']
     */
    public function getPaymentStatus($receiptReference, $fee);

    /**
     * Get the authorisation code for a card payment
     *
     * @param string    $receiptReference receipt reference
     * @param FeeEntity $fee              fee
     *
     * @return string auth code|null
     */
    public function getPaymentAuthCode($receiptReference, $fee);

    /**
     * Record a cash payment in CPMS
     *
     * @param array $fees
     * @param float $amount
     * @param string|DateTime $receiptDate
     * @param string $slipNo paying in slip number
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function recordCashPayment($fees, $amount, $receiptDate, $slipNo);

    /**
     * Record a cheque payment in CPMS
     *
     * @param array $fees
     * @param float $amount
     * @param array $receiptDate (from DateSelect)
     * @param string $payer payer name
     * @param string $slipNo paying in slip number
     * @param string $chequeNo cheque number
     * @param string $chequeDate (from DateSelect)
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function recordChequePayment($fees, $amount, $receiptDate, $payer, $slipNo, $chequeNo, $chequeDate);

    /**
     * Record a Postal Order payment in CPMS
     *
     * @param array $fees
     * @param float $amount
     * @param array $receiptDate (from DateSelect)
     * @param string $slipNo paying in slip number
     * @param string $poNo Postal Order number
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function recordPostalOrderPayment($fees, $amount, $receiptDate, $slipNo, $poNo);

    /**
     * Get a list of available reports
     *
     * @return array
     */
    public function getReportList();

    /**
     * Get a list of stored debit/credit cards references stored in CPMS
     *
     * @param string $isNi
     *
     * @return array
     */
    public function getListStoredCards($isNi);

    /**
     * Request report creation
     *
     * @param string $reportCode
     * @param DateTime $start
     * @param DateTime $end
     * @return array
     */
    public function requestReport($reportCode, \DateTime $start, \DateTime $end);

    /**
     * Check report status by reference
     *
     * @param string $reference
     * @return array
     */
    public function getReportStatus($reference);

    /**
     * Download report by reference
     *
     * @param string $reference
     * @param string $token
     * @return array
     */
    public function downloadReport($reference, $token);

    /**
     * Refund payments in a batch
     *
     * @param FeeEntity $fee
     * @return array
     */
    public function batchRefund($fee);

    /**
     * Reverse a cheque, cash, PO or card payment
     *
     * @param string $receiptReference
     * @param string $paymentMethod original payment method, e.g. 'fpm_cash'
     * @param array $fees needed to get customer reference
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function reversePayment($receiptReference, $paymentMethod, $fees = []);

    /**
     * @return string amount formatted to two decimal places with no thousands separator
     */
    public function formatAmount(mixed $amount);

    /**
     * @return int
     */
    public function getVersion();
}
