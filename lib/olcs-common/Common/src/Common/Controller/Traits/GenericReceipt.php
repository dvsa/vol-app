<?php

namespace Common\Controller\Traits;

use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Common\View\Model\ReceiptViewModel;
use Dvsa\Olcs\Transfer\Query;

/**
 * Generic receipt functionality
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait GenericReceipt
{
    /**
     * Process action - Print
     *
     * @return ReceiptViewModel
     * @throws ResourceNotFoundException
     */
    public function printAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'pay-fees.success.print-title');

        $paymentRef = $this->params()->fromRoute('reference');

        $viewData = $this->getReceiptData($paymentRef);

        return new ReceiptViewModel($viewData);
    }

    /**
     * Request from API receip data by reference
     *
     * @param string $paymentRef Payment Reference
     *
     * @return array
     * @throws ResourceNotFoundException
     */
    protected function getReceiptData($paymentRef)
    {
        $query = Query\Transaction\TransactionByReference::create(['reference' => $paymentRef]);

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleQuery($query);
        if ($response->isOk()) {
            $payment = $response->getResult();
            $fees = array_map(
                static fn($fp) => $fp['fee'],
                $payment['feeTransactions']
            );
        } else {
            throw new ResourceNotFoundException('Payment not found');
        }

        $tableFactory = $this->tableBuilder ?? $this->tableFactory;
        $table = $tableFactory->buildTable('pay-fees', $fees, [], false);

        // override table title
        $table->setVariable('title', $this->translationHelper->translate('pay-fees.success.table.title'));

        // get operator name from the first fee
        $operatorName = $fees[0]['licence']['organisation']['name'];

        return [
            'payment' => $payment,
            'fees' => $fees,
            'table' => $table,
            'operatorName' => $operatorName,
            'hasContinuation' => $this->hasContinuationFee($fees),
        ];
    }

    /**
     * Define have a continuation fee in list of fees
     *
     * @param array $fees List of fees
     *
     * @return bool
     */
    protected function hasContinuationFee($fees)
    {
        foreach ($fees as $fee) {
            if ($fee['feeType']['feeType']['id'] == RefData::FEE_TYPE_CONT) {
                return true;
            }
        }

        return false;
    }
}
