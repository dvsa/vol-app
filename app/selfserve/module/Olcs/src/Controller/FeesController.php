<?php

/**
 * Fees Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller;

use Olcs\View\Model\Fees;
use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Common\Exception\ResourceNotFoundException;
use Common\Service\Entity\FeePaymentEntityService;
use Common\Service\Entity\PaymentEntityService;
use Common\Service\Cpms\Exception as CpmsException;

/**
 * Fees Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeesController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait,
        Lva\Traits\DashboardNavigationTrait;

    /**
     * Fees index action
     */
    public function indexAction()
    {
        $response = $this->checkActionRedirect();
        if ($response) {
            return $response;
        }

        $organisationId = $this->getCurrentOrganisationId();
        $fees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForOrganisation($organisationId);

        if (!empty($fees)) {
            $fees = $fees['Results'];
        }

        $table = $this->getServiceLocator()->get('Table')
            ->buildTable('fees', $this->formatTableData($fees), [], false);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('fees');

        // populate the navigation tabs with correct counts
        $this->populateTabCounts(count($fees));

        return $view;
    }

    /**
     * Pay Fees action
     */
    public function payFeesAction()
    {
        if ($this->getRequest()->isPost() && $this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $fees = $this->getFeesFromParams();

        if (empty($fees)) {
            throw new ResourceNotFoundException('Fee not found');
        }

        if ($this->getRequest()->isPost()) {
            return $this->payFeesViaCpms($fees);
        }

        $form = $this->getForm();
        if (count($fees) > 1) {
            $table = $this->getServiceLocator()->get('Table')
                ->buildTable('pay-fees', $this->formatTableData($fees), [], false);
            $view = new ViewModel(['table' => $table, 'form' => $form]);
            $view->setTemplate('pay-fees');
        } else {
            $fee = array_shift($fees);
            $view = new ViewModel(['fee' => $fee, 'form' => $form]);
            $view->setTemplate('pay-fee');
        }

        return $view;
    }

    public function handleResultAction()
    {
        try {
            $query = (array)$this->getRequest()->getQuery();
            $resultStatus = $this->getServiceLocator()
                ->get('Cpms\FeePayment')
                ->handleResponse($query, FeePaymentEntityService::METHOD_CARD_ONLINE);
        } catch (CpmsException $ex) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToIndex();
        }

        switch ($resultStatus) {
            case PaymentEntityService::STATUS_PAID:
                return $this->redirectToReceipt($query['receipt_reference']);
            case PaymentEntityService::STATUS_FAILED:
            default:
                $this->addErrorMessage('payment-failed');
                // no break
            case PaymentEntityService::STATUS_CANCELLED:
                return $this->redirectToIndex();
        }
    }

    public function receiptAction()
    {
        $paymentRef = $this->params()->fromRoute('reference');

        $viewData = $this->getReceiptData($paymentRef);

        $view = new ViewModel($viewData);
        $view->setTemplate('payment-success');
        return $view;
    }

    public function printAction()
    {
        $paymentRef = $this->params()->fromRoute('reference');

        $viewData = $this->getReceiptData($paymentRef);

        $view = new ViewModel($viewData);
        $view->setTemplate('payment-success-print');
        return $this->render($view);
    }

    /**
     * @param array $fees
     * @return array
     */
    protected function formatTableData($fees)
    {
        $tableData = [];

        if (!empty($fees)) {
            foreach ($fees as $fee) {
                $fee['licNo'] = $fee['licence']['licNo'];
                unset($fee['licence']);
                $tableData[] = $fee;
            }
        }

        return $tableData;
    }

    /**
     * Get fees by ID(s) from params, note these *must* be a subset of the
     * outstanding fees for the current organisation - any invalid IDs are
     * ignored
     */
    protected function getFeesFromParams()
    {
        $fees = [];

        $organisationId = $this->getCurrentOrganisationId();
        $outstandingFees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForOrganisation($organisationId);

        if (!empty($outstandingFees)) {
            $ids = explode(',', $this->params('fee'));
            foreach ($outstandingFees['Results'] as $fee) {
                if (in_array($fee['id'], $ids)) {
                    $fees[] = $fee;
                }
            }
        }

        return $fees;
    }

    protected function getForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('FeePayment');
    }

    protected function checkActionRedirect()
    {
        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();
            if (!isset($data['id']) || empty($data['id'])) {
                $this->addErrorMessage('fees.pay.error.please-select');
                return $this->redirectToIndex();
            }
            $params = [
                'fee' => implode(',', $data['id']),
            ];
            return $this->redirect()->toRoute('fees/pay', $params, null, true);
        }
    }

    protected function redirectToIndex()
    {
        return $this->redirect()->toRoute('fees');
    }

    protected function redirectToReceipt($reference)
    {
        return $this->redirect()->toRoute('fees/receipt', ['reference' => $reference]);
    }

    protected function payFeesViaCpms($fees)
    {
        // Check for and resolve any outstanding payment requests
        $service = $this->getServiceLocator()->get('Cpms\FeePayment');
        $feesToPay = [];
        foreach ($fees as $fee) {
            if ($service->hasOutstandingPayment($fee)) {
                $paid = $service->resolveOutstandingPayments($fee);
                if (!$paid) {
                    $feesToPay[] = $fee;
                }
            } else {
                $feesToPay[] = $fee;
            }
        }
        if (empty($feesToPay)) {
            // fees were all paid
            return $this->redirectToIndex();
        }

        $customerReference = $this->getCurrentOrganisationId();
        $redirectUrl = $this->getServiceLocator()->get('Helper\Url')
            ->fromRoute('fees/result', [], ['force_canonical' => true], true);

        try {
            $response = $service->initiateCardRequest($customerReference, $redirectUrl, $feesToPay);
        } catch (CpmsException\PaymentInvalidResponseException $e) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToIndex();
        }

        $view = new ViewModel(
            [
                'gateway' => $response['gateway_url'],
                'data' => [
                    'receipt_reference' => $response['receipt_reference']
                ]
            ]
        );
        $view->setTemplate('cpms/payment');

        return $this->render($view);
    }

    protected function getReceiptData($paymentRef)
    {
        // @TODO get from backend via paymentRef
        $payment = [
            'guid' => $paymentRef,
        ];
        $fees = [
            [
                'id' => 7,
                'payerName' => 'Dan',
                'description' => 'fee 7',
                'receivedDate' => '2015-02-03T12:34:56+00:00:00',
                'licence' => [
                    'licNo' => 'LIC7',
                ],
                'invoicedDate' => '2015-01-03',
                'amount' => '123.45',
            ],
            [
                'id' => 96,
                'payerName' => 'Dan',
                'description' => 'fee 96',
                'receivedDate' => '2015-02-03T12:34:56+00:00:00',
                'licence' => [
                    'licNo' => 'LIC96',
                ],
                'invoicedDate' => '2015-02-03',
                'amount' => '1234.56',
            ],
        ];
        $operatorName = 'Big Trucks Ltd.';

        $table = $this->getServiceLocator()->get('Table')
                ->buildTable('pay-fees', $this->formatTableData($fees), [], false);

        // override table title
        $tableTitle = $this->getServiceLocator()->get('Helper\Translation')
            ->translate('pay-fees.success.table.title');
        $table->setVariable('title', $tableTitle);

        return compact('payment', 'fees', 'operatorName', 'table');
    }
}
