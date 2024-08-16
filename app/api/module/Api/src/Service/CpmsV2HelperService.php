<?php

/**
 * Cpms Version 2 Helper Service
 *
 * Note: CPMS has been known to reject ints as 'missing', so we cast
 * some fields (ID's, etc.) to strings
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Service;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Service\Cpms\ApiServiceFactory;
use Dvsa\Olcs\Cpms\Service\ApiService;
use Olcs\Logging\Log\Logger;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Cpms Version 2 Helper Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CpmsV2HelperService implements FactoryInterface, CpmsHelperInterface
{
    // CPMS' preferred date format (note: this changed around 03/2015)
    public const DATE_FORMAT = 'Y-m-d';

    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public const REFUND_REASON = 'Refund';

    public const PARAM_CUSTOMER_NAME_LIMIT = 100;
    public const PARAM_CUSTOMER_MANAGER_NAME_LIMIT = 100;
    public const PARAM_RECEIVER_NAME_LIMIT = 150;

    /**
     * @var ApiService
     */
    protected $cpmsClient;

    /**
     *
     * @var string
     */
    private $invoicePrefix;

    /**
     * @var \Dvsa\Olcs\Api\Service\FeesHelperService
     */
    protected $feesHelper;

    /**
     * @var string
     */
    protected $niSchemaId;

    /**
     * @var string
     */
    protected $niClientSecret;

    /**
     * @var string
     */
    protected $schemaId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @return ApiService
     */
    protected function getClient()
    {
        return $this->cpmsClient;
    }

    /**
     * Initiate a card payment
     *
     * @param string $redirectUrl redirect back to here from payment gateway
     * @param array  $fees        fees
     * @param array  $extraParams extra params
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function initiateCardRequest($redirectUrl, array $fees, array $extraParams = [])
    {
        $endPoint = '/api/payment/card';
        $scope    = ApiService::SCOPE_CARD;

        return $this->initiateRequest($redirectUrl, $fees, $endPoint, $scope, $extraParams);
    }

    /**
     * Initiate a stored card payment payment
     *
     * @param string $redirectUrl         redirect back to here from payment gateway
     * @param array  $fees                fees
     * @param string $storedCardReference Stored card reference
     * @param array  $extraParams         extra params
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function initiateStoredCardRequest($redirectUrl, array $fees, $storedCardReference, array $extraParams = [])
    {
        $endPoint = '/api/payment/stored-card/' . $storedCardReference;
        $scope    = ApiService::SCOPE_STORED_CARD;

        return $this->initiateRequest($redirectUrl, $fees, $endPoint, $scope, $extraParams);
    }

    /**
     * Initiate a card not present (CNP) payment
     *
     * @param string $redirectUrl redirect back to here from payment gateway
     * @param array  $fees        fees
     * @param array  $extraParams extra params
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function initiateCnpRequest($redirectUrl, array $fees, $extraParams = [])
    {
        $endPoint = '/api/payment/cardholder-not-present';
        $scope    = ApiService::SCOPE_CNP;

        return $this->initiateRequest($redirectUrl, $fees, $endPoint, $scope, $extraParams);
    }

    /**
     * Initiate a payment request
     *
     * @param string $redirectUrl      redirect back to here from payment gateway
     * @param array  $fees            fees
     * @param string $endPoint        Either card or CNP endpoint
     * @param string $scope           Either ApiService::SCOPE_CNP or ApiService::SCOPE_CARD
     * @param array  $miscExtraParams extra params
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    private function initiateRequest($redirectUrl, array $fees, $endPoint, $scope, $miscExtraParams = [])
    {
        $method   = 'post';
        $extraParams = [
            'redirect_uri' => $redirectUrl,
            'disable_redirection' => true, // legacy??
            'scope' => $scope,
        ];
        $extraParams = array_merge($extraParams, $miscExtraParams);
        $params = $this->getParametersForFees($fees, $extraParams);

        foreach ($fees as $fee) {
            $params['payment_data'][] = $this->getPaymentDataForFee($fee, [], $miscExtraParams);
        }

        $response = $this->send($method, $endPoint, $scope, $params, reset($fees));

        return $this->validatePaymentResponse($response, false);
    }

    /**
     * Update CPMS with payment result
     *
     * @param string $reference payment reference / guid
     * @param array  $data      response data from the payment gateway
     * @param Fee    $fee       fee
     *
     * @return array|mixed response
     * @see CpmsClient\Service\ApiService::put()
     */
    public function handleResponse($reference, $data, $fee = null)
    {
        /**
         * Let CPMS know the response from the payment gateway
         *
         * We have to bundle up the response data verbatim as it can
         * vary per gateway implementation
         */
        $schemaId = ($fee !== null) ? $this->getSchemaId($fee) : $this->schemaId;
        if ($schemaId === $this->niSchemaId) {
            $this->changeSchema($this->niSchemaId, $this->niClientSecret);
        }
        return $this->getClient()->put('/api/gateway/' . $reference . '/complete', ApiService::SCOPE_CARD, $data);
    }

    /**
     * Determine the status of a payment/transaction
     *
     * @param string $receiptReference receipt reference
     * @param Fee    $fee              fee
     *
     * @return array ['code' => payment status code, 'message' => 'CPMS error message']
     */
    public function getPaymentStatus($receiptReference, $fee)
    {
        $method   = 'get';
        $endPoint = '/api/payment/' . $receiptReference;
        $scope    = ApiService::SCOPE_QUERY_TXN;

        $params = [
            'required_fields' => [
                'payment' => [
                    'payment_status'
                ]
            ]
        ];

        $response = $this->send($method, $endPoint, $scope, $params, $fee);

        $statusCode = $response['payment_status']['code'] ?? null;
        $message = ($response['message'] ?? 'No error message from CPMS')
            . ((isset($response['code'])) ? ', code: ' . $response['code'] :  ', no error code from CPMS');

        return [
            'code' => $statusCode,
            'message' => $message
        ];
    }

    /**
     * Get the authorisation code for a card payment
     *
     * Note: this is potentially required for chargebacks, etc. but is not
     * currently used
     *
     * @param string $receiptReference receipt reference
     * @param Fee    $fee              fee
     *
     * @return string auth code|null
     */
    public function getPaymentAuthCode($receiptReference, $fee)
    {
        $method   = 'get';
        $endPoint = '/api/payment/' . $receiptReference . '/auth-code';
        $scope    = ApiService::SCOPE_QUERY_TXN;

        $response = $this->send($method, $endPoint, $scope, [], $fee);
        return $response['auth_code'] ?? null;
    }

    /**
     * Record a cash payment in CPMS
     *
     * @param                 array $fees      fees
     * @param                 float $amount    amount
     * @param string|DateTime $receiptDate     receipt date
     * @param string          $slipNo          paying in slip number
     * @param array           $miscExtraParams misc extra params
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function recordCashPayment($fees, $amount, $receiptDate, $slipNo, $miscExtraParams = [])
    {
        $method   = 'post';
        $endPoint = '/api/payment/cash';
        $scope    = ApiService::SCOPE_CASH;

        $extraParams = [
            'slip_number' => (string) $slipNo,
            'batch_number' => (string) $slipNo,
            'receipt_date' => $this->formatDate($receiptDate),
            'scope' => $scope,
            'total_amount' => $this->formatAmount($amount),
        ];
        $extraParams = array_merge($extraParams, $miscExtraParams);
        $params = $this->getParametersForFees($fees, $extraParams);

        $allocations = $this->feesHelper->allocatePayments($amount, $fees);

        foreach ($fees as $fee) {
            $extraPaymentData = ['allocated_amount' => $allocations[$fee->getId()]];
            $paymentData = $this->getPaymentDataForFee($fee, $extraPaymentData, $miscExtraParams);
            if (!empty($paymentData)) {
                $params['payment_data'][] = $paymentData;
            }
        }

        $response = $this->send($method, $endPoint, $scope, $params, reset($fees));

        return $this->validatePaymentResponse($response);
    }

    /**
     * Record a cheque payment in CPMS
     *
     * @param array  $fees            fees
     * @param float  $amount          amount
     * @param string $receiptDate     (from DateSelect)
     * @param string $payer           payer name
     * @param string $slipNo          paying in slip number
     * @param string $chequeNo        cheque number
     * @param string $chequeDate      (from DateSelect)
     * @param array  $miscExtraParams misc extra params
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function recordChequePayment(
        $fees,
        $amount,
        $receiptDate,
        $payer,
        $slipNo,
        $chequeNo,
        $chequeDate,
        $miscExtraParams = []
    ) {
        $method   = 'post';
        $endPoint = '/api/payment/cheque';
        $scope    = ApiService::SCOPE_CHEQUE;

        $extraParams = [
            'cheque_date' => $this->formatDate($chequeDate),
            'cheque_number' => (string) $chequeNo,
            'slip_number' => (string) $slipNo,
            'batch_number' => (string) $slipNo,
            'receipt_date' => $this->formatDate($receiptDate),
            'name_on_cheque' => $payer,
            'scope' => $scope,
            'total_amount' => $this->formatAmount($amount),
        ];
        $extraParams = array_merge($extraParams, $miscExtraParams);
        $params = $this->getParametersForFees($fees, $extraParams);

        $allocations = $this->feesHelper->allocatePayments($amount, $fees);

        foreach ($fees as $fee) {
            $extraPaymentData = ['allocated_amount' => $allocations[$fee->getId()]];
            $paymentData = $this->getPaymentDataForFee($fee, $extraPaymentData, $miscExtraParams);
            if (!empty($paymentData)) {
                $params['payment_data'][] = $paymentData;
            }
        }

        $response = $this->send($method, $endPoint, $scope, $params, reset($fees));

        return $this->validatePaymentResponse($response);
    }

    /**
     * Record a Postal Order payment in CPMS
     *
     * @param array  $fees            fees
     * @param float  $amount          amount
     * @param string $receiptDate     (from DateSelect)
     * @param string $slipNo          paying in slip number
     * @param string $poNo            Postal Order number
     * @param array  $miscExtraParams misc extra params
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function recordPostalOrderPayment($fees, $amount, $receiptDate, $slipNo, $poNo, $miscExtraParams = [])
    {
        $method   = 'post';
        $endPoint = '/api/payment/postal-order';
        $scope    = ApiService::SCOPE_POSTAL_ORDER;

        $extraParams = [
            'postal_order_number' => (string) $poNo,
            'slip_number' => (string) $slipNo,
            'batch_number' => (string) $slipNo,
            'receipt_date' => $this->formatDate($receiptDate),
            'scope' => $scope,
            'total_amount' => $this->formatAmount($amount),
        ];
        $extraParams = array_merge($extraParams, $miscExtraParams);
        $params = $this->getParametersForFees($fees, $extraParams);

        $allocations = $this->feesHelper->allocatePayments($amount, $fees);

        foreach ($fees as $fee) {
            $extraPaymentData = ['allocated_amount' => $allocations[$fee->getId()]];
            $paymentData = $this->getPaymentDataForFee($fee, $extraPaymentData, $miscExtraParams);
            if (!empty($paymentData)) {
                $params['payment_data'][] = $paymentData;
            }
        }

        $response = $this->send($method, $endPoint, $scope, $params, reset($fees));

        return $this->validatePaymentResponse($response);
    }

    /**
     * Get a list of available reports
     *
     * @return array
     */
    public function getReportList()
    {
        $params = [
            'filters' => [
                'scheme' => [
                    $this->schemaId,
                    $this->niSchemaId
                ],
            ]
        ];
        return $this->send('get', '/api/report', ApiService::SCOPE_REPORT, $params);
    }

    /**
     * Get a list of stored debit/credit cards references stored in CPMS
     *
     * @param string $isNi is NI list
     *
     * @return array
     */
    public function getListStoredCards($isNi)
    {
        return $this->send(
            'get',
            '/api/stored-card',
            ApiService::SCOPE_STORED_CARD,
            [],
            null,
            ($isNi === 'Y') ? $this->niSchemaId : $this->schemaId
        );
    }

    /**
     * Request report creation
     *
     * @param string $reportCode
     * @param DateTime $start
     * @param DateTime $end
     * @return array
     */
    public function requestReport($reportCode, \DateTime $start, \DateTime $end)
    {
        $params = [
            'report_code' => (string) $reportCode,
            'filters' => [
                'from' => $this->formatDateTime($start),
                'to' => $this->formatDateTime($end),
                'scheme' => [
                    $this->schemaId,
                    $this->niSchemaId
                ],
            ],
        ];

        return $this->send('post', '/api/report', ApiService::SCOPE_REPORT, $params);
    }

    /**
     * Check report status by reference
     *
     * @param string $reference
     * @return array
     */
    public function getReportStatus($reference)
    {
        $endPoint = '/api/report/' . $reference . '/status';
        $params = [
            'filters' => [
                'scheme' => [
                    $this->schemaId,
                    $this->niSchemaId
                ],
            ]
        ];

        return $this->send('get', $endPoint, ApiService::SCOPE_REPORT, $params);
    }

    /**
     * Download report by reference
     *
     * @param string $reference
     * @param string $token
     * @return array
     */
    public function downloadReport($reference, $token)
    {
        $url = '/api/report/' . $reference . '/download?token=' . $token;
        $params = [
            'filters' => [
                'scheme' => [
                    $this->schemaId,
                    $this->niSchemaId
                ],
            ]
        ];

        return $this->send('get', $url, ApiService::SCOPE_REPORT, $params);
    }

    /**
     * Refund a fee
     *
     * @param Fee   $fee         fee
     * @param array $extraParams extra params for misc fees
     *
     * @return array of refund receipt references one for each payment. key = payment ref, value = refund ref
     */
    public function refundFee(Fee $fee, $extraParams = [])
    {
        if (count($fee->getFeeTransactionsForRefund()) === 1) {
            return $this->singlePaymentRefund($fee, $extraParams);
        } else {
            return $this->batchRefund($fee, $extraParams);
        }
    }

    /**
     * Refund a fee that has a single payment
     *
     * @param Fee   $fee         fee
     * @param array $extraParams extra params for misc fees
     *
     * @return array key = payment ref, value = refund ref
     * @throws \Dvsa\Olcs\Api\Service\CpmsResponseException
     */
    private function singlePaymentRefund(Fee $fee, $extraParams = [])
    {
        $feeTransactions = $fee->getFeeTransactionsForRefund();
        // get first (and only) fee transaction
        /* @var $ft FeeTransaction */
        $ft = array_shift($feeTransactions);
        $reference = $ft->getTransaction()->getReference();

        $method   = 'post';
        $endPoint = '/api/payment/' . $reference . '/refund';
        $scope    = ApiService::SCOPE_REFUND;

        $params = array_merge(
            $this->getRefundPaymentDataForFeeTransaction($ft, $extraParams),
            [
                'scope' => $scope,
                'total_amount' => $this->formatAmount($ft->getAmount()),
                'country_code' => $fee->getFeeType()->getCountryCode(),
            ]
        );
        $params = array_merge($params, $extraParams);
        $params = $this->addCustomerParams($params, [$fee], $fee);
        $paymentMethod = $ft->getTransaction()->getPaymentMethod()->getId();
        if (in_array($paymentMethod, [Fee::METHOD_CARD_ONLINE, Fee::METHOD_CARD_OFFLINE], true)) {
            $params['auth_code'] = $this->getPaymentAuthCode($reference, $fee);
        }

        $response = $this->send($method, $endPoint, $scope, $params, $fee, $ft->getTransaction()->getCpmsSchema());

        $refundSuccessStatuses = [
            self::PAYMENT_REFUNDED,
            self::PAYMENT_REFUND_REQUESTED
        ];
        if (isset($response['code']) && in_array($response['code'], $refundSuccessStatuses)) {
            return [$ft->getTransaction()->getReference() => $response['receipt_reference']];
        } else {
            $e = new CpmsResponseException('Invalid refund response');
            $e->setResponse($response);
            throw $e;
        }
    }

    /**
     * Refund payments in a batch
     *
     * @param Fee   $fee         fee
     * @param array $extraParams extra params for misc fees
     *
     * @return array one for each fee payment, key = payment ref, value = refund ref
     * @throws CpmsResponseException if response is invalid
     */
    public function batchRefund($fee, $extraParams = [])
    {
        $method   = 'post';
        $endPoint = '/api/refund';
        $scope    = ApiService::SCOPE_REFUND;

        $payments = [];
        $transactionSchemas = [];

        /** @var \Dvsa\Olcs\Api\Entity\Fee\FeeTransaction $ft */
        foreach ($fee->getFeeTransactionsForRefund() as $ft) {
            $payments[] = $this->getRefundPaymentDataForFeeTransaction($ft, $extraParams);
            $transactionSchemas[] = $ft->getTransaction()->getCpmsSchema();
        }

        if (count(array_unique($transactionSchemas)) > 1) {
            throw new CpmsV2HelperServiceException('Cannot refund multiple transactions with different schemas', 400);
        }

        $schema = !empty($transactionSchemas) ? $transactionSchemas[0] : null;

        $params = array_merge(
            [
                'scope' => $scope,
                'payments' => $payments,
                'country_code' => $fee->getFeeType()->getCountryCode(),
            ],
            $extraParams
        );
        $params = $this->addCustomerParams($params, [$fee], $fee);

        $response = $this->send($method, $endPoint, $scope, $params, $fee, $schema);

        if (isset($response['code']) && $response['code'] === self::RESPONSE_SUCCESS) {
            return $response['receipt_references'];
        } else {
            $e = new CpmsResponseException('Invalid refund response');
            $e->setResponse($response);
            throw $e;
        }
    }

    /**
     * Get refund payment data for fee transaction
     *
     * @param FeeTransaction $ft          fee transaction
     * @param array          $extraParams extra params for misc fees
     *
     * @return array of 'payment' data for batch refund call
     * @see https://wiki.i-env.net/display/CPMS/CPMS+API+V2+Specification#CPMSAPIV2Specification-Batchrefund
     */
    protected function getRefundPaymentDataForFeeTransaction(FeeTransaction $ft, $extraParams = [])
    {
        $paymentData = $this->getPaymentDataForFee(
            $ft->getFee(),
            [
                'amount' => $this->formatAmount($ft->getAmount()),
            ],
            $extraParams
        );

        return [
            'country_code' => $ft->getFee()->getFeeType()->getCountryCode(),
            'receipt_reference' => $ft->getTransaction()->getReference(),
            'refund_reason' => self::REFUND_REASON,
            'total_amount' =>  $this->formatAmount($ft->getAmount()),
            'payment_data' => [
                $paymentData,
            ]
        ];
    }

    /**
     * Reverse a cheque, cash, PO or card payment
     *
     * @param string $receiptReference  receipt reference
     * @param string $paymentMethod     original payment method, e.g. 'fpm_cash'
     * @param array  $fees              needed to get customer reference
     * @param array  $extraParams       extra params
     *
     * @return array CPMS response data
     * @throws CpmsResponseException if response is invalid
     */
    public function reversePayment($receiptReference, $paymentMethod, $fees = [], $extraParams = [])
    {
        $method   = 'post';
        $endPoint = '/api/payment/' . $receiptReference . '/reversal';

        $scopeMap = [
            Fee::METHOD_CHEQUE       => ApiService::CHEQUE_RD,
            Fee::METHOD_CARD_ONLINE  => ApiService::SCOPE_CHARGE_BACK,
            Fee::METHOD_CARD_OFFLINE => ApiService::SCOPE_CHARGE_BACK,
            Fee::METHOD_CASH         => ApiService::SCOPE_CASH,
            Fee::METHOD_POSTAL_ORDER => ApiService::SCOPE_POSTAL_ORDER,
        ];

        $scope = $scopeMap[$paymentMethod];

        if (in_array($paymentMethod, [Fee::METHOD_CARD_ONLINE, Fee::METHOD_CARD_OFFLINE])) {
            // for card reversals, switch endpoint to 'charge back'
            $endPoint = '/api/payment/' . $receiptReference . '/chargeback';
        }

        $firstFee = reset($fees);
        $params = array_merge(['scope' => $scope], $extraParams);
        $params = $this->addCustomerParams($params, $fees, $firstFee);

        $response = $this->send($method, $endPoint, $scope, $params, $firstFee);

        return $this->validatePaymentResponse($response, false);
    }

    /**
     * @param mixed $amount
     * @return string amount formatted to two decimal places with no thousands separator
     */
    public function formatAmount($amount)
    {
        return sprintf("%1\$.2f", $amount);
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return 2;
    }

    /**
     * Format a date as required by CPMS payment reference fields
     *
     * @param string|DateTime $date
     * @return string
     */
    protected function formatDate($date)
    {
        if (!is_null($date)) {
            if (is_string($date)) {
                $date = new DateTime($date);
            }
            return $date->format(self::DATE_FORMAT);
        }
    }

    /**
     * Format a date/time as required by CPMS report filter fields
     *
     * @param DateTime $dateTime
     * @return string
     */
    protected function formatDateTime(\DateTime $dateTime)
    {
        return $dateTime->format(self::DATETIME_FORMAT);
    }

    /**
     * Format address
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\Address|array $address address
     *
     * @return array|null
     */
    protected function formatAddress($address)
    {
        if ($address === null) {
            return null;
        }
        if (is_array($address)) {
            return [
                'line_1' => $address['addressLine1'],
                'line_2' => $address['addressLine2'],
                'line_3' => $address['addressLine3'],
                'line_4' => $address['addressLine4'],
                'city' => $address['town'],
                // @see OLCS-14086, agreed solutions is to pass space if the postcode is empty
                'postcode' => empty($address['postcode']) ? ' ' : $address['postcode'],
            ];
        }
        $postcode = $address->getPostcode();
        return [
            'line_1' => $address->getAddressLine1(),
            'line_2' => $address->getAddressLine2(),
            'line_3' => $address->getAddressLine3(),
            'line_4' => $address->getAddressLine4(),
            'city' => $address->getTown(),
            // @see OLCS-14086, agreed solutions is to pass space if the postcode is empty
            'postcode' => empty($postcode)  ? ' ' : $postcode,
        ];
    }

    /**
     * @param array $fees
     * return string
     */
    protected function getTotalAmountFromFees($fees)
    {
        $totalAmount = 0;
        foreach ($fees as $fee) {
            $totalAmount += Fee::amountToPence($fee->getOutstandingAmount());
        }
        return $this->formatAmount(Fee::amountToPounds($totalAmount));
    }

    /**
     * Small helper to check if response was successful
     * (We require a successful response code AND a receipt reference)
     * Returns the response if OK, otherwise throws an exception
     *
     * @param array $response response data
     * @param boolean $requireSuccessCode
     * @return array
     * @throws CpmsResponseException
     */
    protected function validatePaymentResponse($response, $requireSuccessCode = true)
    {

        // check it's an array
        if (is_array($response)) {
            // check we have receipt reference
            if (isset($response['receipt_reference']) && !empty($response['receipt_reference'])) {
                // check we have a success code if required
                if (!$requireSuccessCode) {
                    return $response;
                }
                if (isset($response['code']) && $response['code'] === self::RESPONSE_SUCCESS) {
                    return $response;
                }
            }
        }

        $e = new CpmsResponseException('Invalid payment response');
        $e->setResponse($response);
        throw $e;
    }

    /**
     * Gets Customer Reference based on the fees details
     * The method assumes that all fees link to the same organisationId
     *
     * @param array $fees fees
     *
     * @return int organisationId
     */
    protected function getCustomerReference($fees)
    {
        $reference = null;

        foreach ($fees as $fee) {
            if (!empty($fee->getCustomerReference())) {
                $reference = $fee->getCustomerReference();
                break;
            }
        }

        return $reference;
    }

    /**
     * Gets Receiver Reference based on the fees details
     *
     * @param Fee $fee fee
     *
     * @return string|null
     */
    protected function getReceiverReference($fee)
    {
        $feeLicence = $fee->getLicence();
        $feeType = $fee->getFeeType();
        $feeOrg = $fee->getOrganisation();

        if ($feeType->isMiscellaneous()) {
            return null;
        }

        // IRFO fees
        if ($feeType->getIrfoFeeType() !== null && $feeOrg !== null) {
            $orgId = $feeOrg->getId();
            return 'IR' . str_pad($orgId, 7, '0', STR_PAD_LEFT);
        }

        // All bus fees linked to a licence
        if (
            in_array($feeType->getFeeType()->getId(), [FeeTypeEntity::FEE_TYPE_BUSAPP, FeeTypeEntity::FEE_TYPE_BUSVAR])
            && $feeLicence !== null
        ) {
            return $feeLicence->getLicNo() . 'B';
        }

        // All other fees linked to a licence
        if ($feeLicence !== null) {
            return $feeLicence->getLicNo();
        }

        // All fees not linked to a licence
        if ($feeOrg !== null) {
            return $feeOrg->getId();
        }

        return null;
    }

    /**
     * Get data for 'payment_data' elements of a payment request
     *
     * @param Fee   $fee              fee
     * @param array $extraPaymentData data
     * @param array $miscExtraParams  extra params
     *
     * @return array|null (will return null if we don't want to include a fee,
     * e.g. overpayment balancing fees)
     */
    protected function getPaymentDataForFee(Fee $fee, $extraPaymentData = [], $miscExtraParams = [])
    {
        if ($fee->isBalancingFee()) {
            return;
        }

        $receiverReference = isset($miscExtraParams['customer_reference'])
            ? (string) $miscExtraParams['customer_reference']
            : (string) $this->getReceiverReference($fee);

        $receiverName = $this->truncate(
            $miscExtraParams['customer_name'] ?? $fee->getCustomerNameForInvoice(),
            self::PARAM_RECEIVER_NAME_LIMIT
        );

        $receiverAddress = $this->formatAddress(
            $miscExtraParams['customer_address'] ?? $fee->getCustomerAddressForInvoice()
        );
        $this->validateReceiverParams(compact('receiverReference', 'receiverName', 'receiverAddress'));

        $commonPaymentData = [
            'line_identifier' => (string) $fee->getId(),
            'amount' => $this->formatAmount($fee->getGrossAmount()),
            'allocated_amount' => $this->formatAmount(
                // may be overridden if under/overpayment
                $fee->getOutstandingAmount()
            ),
            // all fees are currently zero rated
            'net_amount' => $this->formatAmount($fee->getNetAmount()),
            'tax_amount' => $this->formatAmount($fee->getVatAmount()),
            'tax_code' => $fee->getFeeType()->getVatCode(),
            'tax_rate' => $fee->getFeeType()->getVatRate(),
            'invoice_date' => $this->formatDate($fee->getInvoicedDate()),
            'sales_reference' => $this->getInvoicePrefix() . (string) $fee->getId(),
            // note, as per OLCS-11438 product_reference should come from the
            // fee_type description, NOT the product_reference column!
            'product_reference' => $fee->getFeeType()->getDescription(),
            'product_description' => $fee->getFeeType()->getDescription(),
            'receiver_reference' => $receiverReference,
            'receiver_name' => $receiverName,
            'receiver_address' => $receiverAddress,
            'rule_start_date' => $this->formatDate($fee->getRuleStartDate()),
            'deferment_period' => (string) $fee->getDefermentPeriod(),
            'country_code' => $fee->getFeeType()->getCountryCode(),
            'sales_person_reference' => $fee->getSalesPersonReference(),
        ];

        return array_merge($commonPaymentData, $extraPaymentData);
    }

    /**
     * Validate receiver fields
     *
     * @param array $params params
     *
     * @return void
     * @throws ValidationException
     */
    protected function validateReceiverParams($params = null)
    {
        $messages = [];

        if (empty($params['receiverReference'])) {
            $messages[] = 'Receiver reference should not be empty';
        }
        if (empty($params['receiverName'])) {
            $messages[] = 'Receiver reference should not be empty';
        }
        if (empty($params['receiverAddress'])) {
            $messages[] = 'Receiver address should not be empty';
        }

        if (count($messages)) {
            throw new ValidationException($messages);
        }
    }

    /**
     * Get top-level data for a payment request
     *
     * @param array $fees        array of Fee objects
     * @param array $extraParams extra params
     *
     * @return array
     */
    protected function getParametersForFees(array $fees, array $extraParams = [])
    {
        if (empty($fees)) {
            return [];
        }

        $totalAmount = $this->getTotalAmountFromFees($fees);
        $firstFee = reset($fees);
        $commonParams = [
            'payment_data' => [],
            'total_amount' => $this->formatAmount($totalAmount),
            'refund_overpayment' => $this->isOverpayment($fees),
            'country_code' => $firstFee->getFeeType()->getCountryCode(),
        ];
        $params = array_merge($commonParams, $extraParams);
        $params = $this->addCustomerParams($params, $fees, $firstFee);

        return $params;
    }

    /**
     * Validate customer params
     *
     * @param array $params params
     *
     * @return void
     * @throws ValidationException
     */
    protected function validateCustomerParams($params = null)
    {
        $messages = [];

        if (empty($params['customer_reference'])) {
            $messages[] = 'Customer reference should not be empty';
        }
        if (empty($params['customer_name'])) {
            $messages[] = 'Customer name should not be empty';
        }
        if (empty($params['customer_address'])) {
            $messages[] = 'Customer address should not be empty';
        }

        if (count($messages)) {
            throw new ValidationException($messages);
        }
    }

    /**
     * Determine if an array of fees contains an overpayment
     *
     * @return boolean
     */
    protected function isOverpayment($fees)
    {
        foreach ($fees as $fee) {
            if ($fee->isBalancingFee()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send a request via the CPMS client and log request/response
     *
     * @param string $endPoint endPoint
     * @param string $scope    scope
     * @param array  $params   params
     * @param Fee    $fee      fee
     * @param string $schemaId schemaId
     *
     * @return array|mixed cpms client response
     */
    protected function send($method, $endPoint, $scope, $params, $fee = null, $schemaId = null)
    {
        $method = strtolower((string) $method);

        $this->debug(
            "CPMS $scope request",
            [
                'endPoint' => $endPoint,
                'scope'    => $scope,
                'params'   => $params,
            ]
        );

        if ($schemaId === null) {
            $schemaId = ($fee instanceof Fee) ? $this->getSchemaId($fee) : $this->schemaId;
        }

        if ($schemaId === $this->niSchemaId) {
            $this->changeSchema($this->niSchemaId, $this->niClientSecret);
        } else {
            $this->changeSchema($this->schemaId, $this->clientSecret);
        }

        $response = $this->getClient()->$method($endPoint, $scope, $params);
        if (is_array($response) && array_key_exists('receipt_reference', $response)) {
            $response['schema_id'] = $schemaId;
        }
        $this->debug("CPMS $scope response", ['response' => $response]);

        return $response;
    }

    protected function debug($message, $data)
    {
        return Logger::debug(
            $message,
            [
                'data' => array_merge(
                    [
                        'version' => $this->getVersion(),
                        'domain' => $this->getClient()->getOptions()->getDomain(),
                    ],
                    $data
                ),
            ]
        );
    }

    /**
     * Set a prefix for the invoice number
     *
     * @param string $prefix
     *
     * @throws \RuntimeException
     */
    public function setInvoicePrefix($prefix)
    {
        if (strlen($prefix) > 8) {
            throw new \RuntimeException('Invoice prefix needs to be less than 8 chars');
        }

        $this->invoicePrefix = $prefix;
    }

    /**
     * Get the invoice prefix
     *
     * @return string
     */
    public function getInvoicePrefix()
    {
        return $this->invoicePrefix;
    }

    /**
     * Truncate a string
     *
     * @param string $text   String to truncate
     * @param int    $length Number of chars to truncate to
     *
     * @return string The truncated string
     */
    private function truncate($text, $length)
    {
        return substr($text, 0, $length);
    }

    /**
     * Process misc params
     *
     * @param array $params   params
     * @param array $fees     fees
     * @param Fee   $firstFee first fee
     *
     * @return array
     * @throws ValidationException
     */
    private function addCustomerParams($params = [], $fees = [], $firstFee = null)
    {
        if (!isset($params['customer_reference'])) {
            $params['customer_reference'] = $this->getCustomerReference($fees);
        }

        if (!isset($params['customer_name'])) {
            $params['customer_name'] = $firstFee->getCustomerNameForInvoice();
            $params['customer_manager_name'] = $firstFee->getCustomerNameForInvoice();
        } else {
            $params['customer_manager_name'] = $params['customer_name'];
        }

        if (!isset($params['customer_address'])) {
            $params['customer_address'] = $firstFee->getCustomerAddressForInvoice();
        }

        $this->validateCustomerParams($params);

        $params['customer_reference'] = (string) $params['customer_reference'];
        $params['customer_name'] = $this->truncate($params['customer_name'], self::PARAM_CUSTOMER_NAME_LIMIT);
        $params['customer_address'] = $this->formatAddress($params['customer_address']);
        $params['customer_manager_name'] = $this->truncate(
            $params['customer_manager_name'],
            self::PARAM_CUSTOMER_MANAGER_NAME_LIMIT
        );
        return $params;
    }

    /**
     * Get schema id
     *
     * @param Fee $fee fee
     *
     * @return string
     */
    private function getSchemaId($fee)
    {
        return $fee->getFeeType()->getIsNi() === 'Y' ? $this->niSchemaId : $this->schemaId;
    }

    /**
     * Change schema id and client secret
     *
     * @param string $schemaId     schema id
     * @param string $clientSecret client secret
     */
    private function changeSchema($schemaId, $clientSecret)
    {
        $identity = $this->getClient()->getIdentity();
        $identity->setClientId($schemaId);
        $identity->setClientSecret($clientSecret);
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (isset($config['cpms']['invoice_prefix'])) {
            $this->setInvoicePrefix($config['cpms']['invoice_prefix']);
        }
        $this->niSchemaId = $config['cpms_credentials']['client_id_ni'] ?? null;
        $this->niClientSecret = $config['cpms_credentials']['client_secret_ni'] ?? null;
        $this->schemaId = $config['cpms_credentials']['client_id'] ?? null;
        $this->clientSecret = $config['cpms_credentials']['client_secret'] ?? null;
        $this->cpmsClient = $container->get(ApiServiceFactory::class);
        $this->feesHelper = $container->get('FeesHelperService');
        return $this;
    }
}
