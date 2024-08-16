<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Fee;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepository;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;

/**
 * Fee
 */
class Fee extends AbstractQueryHandler
{
    protected $repoServiceName = 'Fee';

    /**
     * Handle a Fee query
     *
     * @param QueryInterface $query The query to handle
     *
     * @return Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var FeeRepository $repo */
        $repo = $this->getRepo();
        $repo->disableSoftDeleteable();

        $fee = $repo->fetchUsingId($query);

        return $this->result(
            $fee,
            [
                'feeTransactions' => [
                    'transaction' => [
                        'paymentMethod',
                        'processedByUser',
                        'status',
                        'type',
                    ],
                ],
            ],
            $this->getAdditionalFeeData($fee)
        );
    }

    /**
     * Get additional data from a fee
     *
     * @param FeeEntity $fee The fee from which to get additional data
     *
     * @return array
     */
    private function getAdditionalFeeData(FeeEntity $fee)
    {
        $licence = $fee->getLicence();
        $licenceExpiryDate = ($licence === null) ? null : $licence->getExpiryDate();
        return [
            'allowEdit' => $fee->allowEdit(),
            'outstanding' => $fee->getOutstandingAmount(),
            'customerReference' => $fee->getCustomerReference(),
            // fields that the frontend may expect as they were previously
            // on the fee table
            'receiptNo' => $fee->getLatestPaymentRef(),
            'paymentMethod' => $fee->getPaymentMethod(),
            'processedBy' => $fee->getProcessedBy(),
            'payer' => $fee->getPayer(),
            'slipNo' => $fee->getSlipNo(),
            'chequePoNumber' => $fee->getChequePoNumber(),
            'waiveReason' => $fee->getWaiveReason(),
            'hasOutstandingWaiveTransaction' => !empty($fee->getOutstandingWaiveTransaction()),
            'canRefund' => $fee->canRefund(),
            'displayTransactions' => $this->getDisplayTransactions($fee),
            'vatInfo' => $this->getVatInfo($fee),
            'licenceExpiryDate' => $licenceExpiryDate,
        ];
    }


    /**
     * Filter and group transaction data for display
     *
     * @param FeeEntity $fee The Fee from which to get transaction data for display
     *
     * @return array
     */
    private function getDisplayTransactions(FeeEntity $fee)
    {
        $displayData = [];

        /** @var $ft \Dvsa\Olcs\Api\Entity\Fee\FeeTransaction */
        foreach ($fee->getFeeTransactions() as $ft) {
            $transaction = $ft->getTransaction();
            $id = $transaction->getId();
            if ($transaction->isOutstanding() && $transaction->isWaive()) {
                continue;
            }
            if (isset($displayData[$id])) {
                $displayData[$id]['amount'] += $ft->getAmount();
                continue;
            }
            $displayData[$id] = [
                'transactionId' => $transaction->getId(),
                'type' => $transaction->getType()->getDescription(),
                'completedDate' => $transaction->getCompletedDate(),
                'createdOn' => $transaction->getCreatedOn(),
                'method' => $this->getMethod($transaction),
                'processedBy' => $transaction->getProcessedByFullName(),
                'amount' => $ft->getAmount(),
                'migratedFromOlbs' => $transaction->isMigrated(),
                'status' => $transaction->getStatus(),
            ];
        }

        return $displayData;
    }

    /**
     * Get the transaction method for display
     *
     * @param TransactionEntity $transaction the transaction from which to get the method
     *
     * @return string
     */
    private function getMethod(TransactionEntity $transaction)
    {
        $method = $transaction->getPaymentMethod() ? $transaction->getPaymentMethod()->getDescription() : '';

        return $method . ' ' . $transaction->getDisplayAmount();
    }

    /**
     * Get the VAT for a fee
     *
     * @param FeeEntity $fee the fee for which to get VAT
     *
     * @return string|null e.g. "20% (S)"
     */
    private function getVatInfo($fee)
    {
        $feeType = $fee->getFeeType();

        if ($feeType->getVatRate() > 0) {
            return sprintf('%d%% (%s)', $feeType->getVatRate(), $feeType->getVatCode());
        }
    }
}
