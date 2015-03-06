<?php

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Zend\View\Model\ViewModel;
use Common\Service\Listener\FeeListenerService;
use Common\Service\Entity\FeeEntityService;
use Common\Service\Entity\PaymentEntityService;
use Common\Service\Entity\FeePaymentEntityService;
use Common\Service\Cpms\Exception\PaymentNotFoundException;
use Common\Service\Cpms\Exception\PaymentInvalidStatusException;
use Common\Service\Cpms\Exception\PaymentInvalidResponseException;
use Common\Service\Cpms\Exception\PaymentInvalidTypeException;
use Common\Form\Elements\Validators\FeeAmountValidator;
use Common\Service\Cpms\FeePaymentCpmsService;

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait FeesActionTrait
{
    /**
     * Must be declared by implementing classes in an attempt to
     * try and sanitise template nuances between apps & licences
     */
    abstract protected function renderLayout($view);

    /**
     * Defines the controller specific fees route
     */
    protected abstract function getFeesRoute();

    /**
     * Defines the controller specific fees route params
     */
    protected abstract function getFeesRouteParams();

    /**
     * Defines the controller specific fees table params
     */
    protected abstract function getFeesTableParams();

    /**
     * Shows fees table
     */
    public function feesAction()
    {
        $response = $this->checkActionRedirect();
        if ($response) {
            return $response;
        }

        return $this->commonFeesAction();
    }

    /**
     * Pay Fees Action
     */
    public function payFeesAction()
    {
        $this->pageLayout = null;
        return $this->commonPayFeesAction();
    }

    /**
     * Common logic when rendering the list of fees
     */
    protected function commonFeesAction()
    {
        $this->loadScripts(['forms/filter', 'table-actions']);

        $status = $this->params()->fromQuery('status');
        $filters = [
            'status' => $status
        ];

        $table = $this->getFeesTable($status);

        $view = new ViewModel(
            [
                'table' => $table,
                'form'  => $this->getFeeFilterForm($filters)
            ]
        );
        $view->setTemplate('layout/fees-list');
        return $this->renderLayout($view);
    }

    protected function checkActionRedirect()
    {
        if ($this->getRequest()->isPost()) {

            $data = (array)$this->getRequest()->getPost();
            if (!isset($data['id']) || empty($data['id'])) {
                $this->addErrorMessage('Please select at least one item');
                return $this->redirectToList();
            }

            // @NOTE: only one action supported at the moment, so no need to inspect
            // it. Update logic as and when this needs to change...

            $params = [
                'action' => 'pay-fees',
                'fee' => implode(',', $data['id'])
            ];

            return $this->redirect()->toRoute(
                $this->getFeesRoute() . '/fee_action',
                $params,
                null,
                true
            );
        }
    }

    /**
     * Get fee filter form
     *
     * @param array $filters
     * @return Zend\Form\Form
     */
    protected function getFeeFilterForm($filters = [])
    {
        $form = $this->getForm('fee-filter');
        $form->remove('csrf');
        $form->setData($filters);

        return $form;
    }

    /**
     * Get fees table
     *
     * @param string $status
     * @return Common\Service\Table\TableBuilder;
     */
    protected function getFeesTable($status)
    {
        switch ($status) {
            case 'historical':
                $feeStatus = sprintf(
                    'IN ["%s","%s","%s"]',
                    FeeEntityService::STATUS_PAID,
                    FeeEntityService::STATUS_WAIVED,
                    FeeEntityService::STATUS_CANCELLED
                );
                break;

            case 'all':
                $feeStatus = '';
                break;

            case 'current':
            default:
                $feeStatus = sprintf(
                    'IN ["%s","%s"]',
                    FeeEntityService::STATUS_OUTSTANDING,
                    FeeEntityService::STATUS_WAIVE_RECOMMENDED
                );
        }

        $params = array_merge(
            $this->getFeesTableParams(),
            [
                'page'    => $this->params()->fromQuery('page', 1),
                'sort'    => $this->params()->fromQuery('sort', 'receivedDate'),
                'order'   => $this->params()->fromQuery('order', 'DESC'),
                'limit'   => $this->params()->fromQuery('limit', 10)
            ]
        );

        if ($feeStatus) {
            $params['feeStatus'] = $feeStatus;
        }

        $feesService = $this->getServiceLocator()->get('Olcs\Service\Data\Fee');
        $results = $feesService->getFees($params, null);

        $tableParams = array_merge($params, ['query' => $this->getRequest()->getQuery()]);
        $table = $this->getTable('fees', $results, $tableParams);

        return $table;
    }

    /**
     * Display fee info and edit waive note
     */
    public function editFeeAction()
    {
        $id = $this->params()->fromRoute('fee', null);
        $feesService = $this->getServiceLocator()->get('Olcs\Service\Data\Fee');
        $fee = $feesService->getFee($id);

        // The statuses for which to remove the form
        $noFormStates = [
            FeeEntityService::STATUS_PAID,
            FeeEntityService::STATUS_CANCELLED
        ];

        $form = null;

        if (!in_array($fee['feeStatus']['id'], $noFormStates)) {
            $form = $this->alterFeeForm($this->getForm('fee'), $fee['feeStatus']['id']);
            $form = $this->setDataFeeForm($fee, $form);
            $this->processForm($form);
        }

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }

        $viewParams = [
            'form' => $form,
            'invoiceNo' => $fee['id'],
            'description' => $fee['description'],
            'amount' => $fee['amount'],
            'created' => $fee['invoicedDate'],
            'status' => isset($fee['feeStatus']['description']) ? $fee['feeStatus']['description'] : '',
            'receiptNo' => $fee['receiptNo'],
            'receivedAmount' => $fee['receivedAmount'],
            'receivedDate' => $fee['receivedDate'],
            'paymentMethod' => isset($fee['paymentMethod']['description']) ? $fee['paymentMethod']['description'] : '',
            'processedBy' => isset($fee['lastModifiedBy']['name']) ? $fee['lastModifiedBy']['name'] : '',
            'payer' => isset($fee['payerName']) ? $fee['payerName'] : '',
            'slipNo' => isset($fee['payingInSlipNumber']) ? $fee['payingInSlipNumber'] : '',
            'chequeNo' => '',
            'poNo' => '',
        ];
        // ensure cheque/PO number goes in the correct field
        if (isset($fee['chequePoNumber']) && !empty($fee['chequePoNumber'])) {
            switch ($fee['paymentMethod']['id']) {
                case FeePaymentEntityService::METHOD_CHEQUE:
                    $viewParams['chequeNo'] = $fee['chequePoNumber'];
                    break;
                case FeePaymentEntityService::METHOD_POSTAL_ORDER:
                    $viewParams['poNo'] = $fee['chequePoNumber'];
                    break;
                default:
                    break;
            }
        }

        $view = new ViewModel($viewParams);
        $view->setTemplate('pages/licence/edit-fee.phtml');

        return $this->renderView($view, 'No # ' . $fee['id']);
    }

    /**
     * Common logic when handling payFeesAction
     */
    protected function commonPayFeesAction()
    {
        $fees = $this->getFeesFromParams();
        $maxAmount = 0;
        $service = $this->getServiceLocator()->get('Cpms\FeePayment');

        $outstandingPaymentsResolved = false;
        foreach ($fees as $fee) {
            // bail early if any of the fees prove to be the wrong status
            if ($fee['feeStatus']['id'] !== FeeEntityService::STATUS_OUTSTANDING) {
                $this->addErrorMessage('You can only pay outstanding fees');
                return $this->redirectToList();
            }

            // check for and resolve any outstanding payment requests
            if ($service->hasOutstandingPayment($fee)) {
                $service->resolveOutstandingPayments($fee, FeePaymentEntityService::METHOD_CARD_OFFLINE);
                $outstandingPaymentsResolved = true;
            }

            $maxAmount += $fee['amount'];
        }

        if ($outstandingPaymentsResolved) {
            // Because there could have been multiple fees and payments
            // outstanding we can't easily manage the UX, so bail out gracefully
            // once everything is resolved.
            $this->addWarningMessage(
                'The selected fee(s) had one or more outstanding payment requests '
                .' which are now resolved. Please try again.'
            );
            return $this->redirectToList();
        }

        $form = $this->getForm('FeePayment');

        $form->get('details')
            ->get('maxAmount')
            ->setValue('£' . number_format($maxAmount, 2));

        // conditional validation needs a numeric value to compare
        $form->get('details')
            ->get('feeAmountForValidator')
            ->setValue($maxAmount);

        // default the receipt date to 'today'
        $today = $this->getServiceLocator()->get('Helper\Date')->getDateObject();
        $form->get('details')
            ->get('receiptDate')
            ->setValue($today);

        $form->getInputFilter()
            ->get('details')
            ->get('received')
            ->getValidatorChain()
            ->addValidator(
                new FeeAmountValidator(
                    [
                        'max' => $maxAmount,
                        'inclusive' => true
                    ]
                )
            );

        $this->loadScripts(['forms/fee-payment']);

        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();

            if ($service->isCardPayment($data)) {

                $this->getServiceLocator()
                    ->get('Helper\Form')
                    ->remove($form, 'details->received');
            }

            $form->setData($data);

            if ($form->isValid()) {
                return $this->initiateCpmsRequest($fees, $data['details']);
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        $title = 'Pay fee';
        if (count($fees) !== 1) {
            $title .= 's';
        }
        return $this->renderView($view, $title);
    }

    /**
     * Alter fee form
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function alterFeeForm($form, $status)
    {
        switch ($status) {
            case FeeEntityService::STATUS_OUTSTANDING:
                $form->get('form-actions')->remove('approve');
                $form->get('form-actions')->remove('reject');
                break;

            case FeeEntityService::STATUS_WAIVE_RECOMMENDED:
                $form->get('form-actions')->remove('recommend');
                break;

            case FeeEntityService::STATUS_WAIVED:
                $form->remove('form-actions');
                $form->get('fee-details')->get('waiveReason')->setAttribute('disabled', 'disabled');
                break;
        }
        return $form;
    }

    /**
     * Process form
     *
     * @param Zend\Form\Form $form
     */
    protected function processForm($form)
    {
        if ($this->isButtonPressed('recommend')) {
            $this->formPost($form, 'recommendWaive');
        } elseif ($this->isButtonPressed('reject')) {
            $this->validateForm = false;
            $this->formPost($form, 'rejectWaive');
        } elseif ($this->isButtonPressed('approve')) {
            $this->formPost($form, 'approveWaive');
        } elseif ($this->isButtonPressed('cancel')) {
            $this->redirectToList();
        }
    }

    /**
     * Recommend waive
     *
     * @param array $data
     */
    protected function recommendWaive($data)
    {
        $params = [
            'id' => $data['fee-details']['id'],
            'version' => $data['fee-details']['version'],
            'waiveReason' => $data['fee-details']['waiveReason'],
            'feeStatus' => FeeEntityService::STATUS_WAIVE_RECOMMENDED
        ];
        $this->updateFeeAndRedirectToList($params);
    }

    /**
     * Reject waive
     *
     * @param array $data
     */
    protected function rejectWaive($data)
    {
        $params = [
            'id' => $data['fee-details']['id'],
            'version' => $data['fee-details']['version'],
            'feeStatus' => FeeEntityService::STATUS_OUTSTANDING
        ];
        $message = 'The fee waive recommendation has been rejected';
        $this->updateFeeAndRedirectToList($params, $message);
    }

    /**
     * Approve waive
     *
     * @param array $data
     */
    protected function approveWaive($data)
    {
        $params = [
            'id' => $data['fee-details']['id'],
            'version' => $data['fee-details']['version'],
            'waiveReason' => $data['fee-details']['waiveReason'],
            'paymentMethod'  => FeePaymentEntityService::METHOD_WAIVE,
            'feeStatus' => FeeEntityService::STATUS_WAIVED
        ];

        $this->getServiceLocator()->get('Entity\Fee')->save($params);
        $this->addSuccessMessage('The selected fee has been waived');

        $this->getServiceLocator()->get('Listener\Fee')->trigger(
            $data['fee-details']['id'],
            FeeListenerService::EVENT_WAIVE
        );

        $this->redirectToList();
    }

    /**
     * Update fee and redirect to list
     *
     * @param array $data
     * @param string $message
     */
    protected function updateFeeAndRedirectToList($data, $message = '')
    {
        $this->getServiceLocator()->get('Entity\Fee')->save($data);
        if ($message) {
            $this->addSuccessMessage($message);
        }
        $this->redirectToList();
    }

    /**
     * Set data
     *
     * @param array $fee
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function setDataFeeForm($fee, $form)
    {
        if ($form) {
            $form->get('fee-details')->get('id')->setValue($fee['id']);
            $form->get('fee-details')->get('version')->setValue($fee['version']);
            $form->get('fee-details')->get('waiveReason')->setValue($fee['waiveReason']);
        }
        return $form;
    }

    /**
     * Redirect back to list of fees
     *
     * @return redirect
     */
    protected function redirectToList()
    {
        $route = $this->getFeesRoute();
        $params = $this->getFeesRouteParams();
        return $this->redirect()->toRouteAjax($route, $params);
    }

    /**
     * Gets Customer Reference based on the fees details
     * The method assumes that all fees link to the same organisationId
     *
     * @param array $fees
     * @return int organisationId
     */
    protected function getCustomerReference($fees)
    {
        if (empty($fees)) {
            return null;
        }

        $organisationId = null;

        foreach ($fees as $fee) {
            if (empty($fee) || empty($fee['id'])) {
                continue;
            }
            $organisation = $this->getServiceLocator()
                ->get('Entity\Fee')
                ->getOrganisation($fee['id']);

            if (!empty($organisation) && !empty($organisation['id'])) {
                $organisationId = $organisation['id'];
                break;
            }
        }

        return $organisationId;
    }

    /**
     * Kick off the CPMS payment process for a given amount
     * relating to a given array of fees
     *
     * @param array  $fees
     * @param array  $details
     */
    private function initiateCpmsRequest($fees, $details)
    {
        $paymentType = $details['paymentType'];
        if (!$this->getServiceLocator()->get('Entity\FeePayment')->isValidPaymentType($paymentType)) {
            throw new PaymentInvalidTypeException($paymentType . ' is not a recognised payment type');
        }

        switch ($paymentType) {
            case FeePaymentEntityService::METHOD_CASH:
            case FeePaymentEntityService::METHOD_CHEQUE:
            case FeePaymentEntityService::METHOD_POSTAL_ORDER:
                // we don't support cash/cheque/po payments for multiple fees
                if (count($fees)!==1) {
                    throw new \Common\Exception\BadRequestException(
                        'Payment of multiple fees by cash/cheque/PO not supported'
                    );
                }
                $fee = $fees[0];
                $amount = number_format($details['received'], 2);
                break;
            default:
                break;
        }

        $customerReference = $this->getCustomerReference($fees);

        switch ($paymentType) {
            case FeePaymentEntityService::METHOD_CARD_OFFLINE:
                $redirectUrl = $this->url()->fromRoute(
                    $this->getFeesRoute() . '/fee_action',
                    ['action' => 'payment-result'],
                    ['force_canonical' => true],
                    true
                );

                try {
                    $response = $this->getServiceLocator()
                        ->get('Cpms\FeePayment')
                        ->initiateCardRequest(
                            $customerReference,
                            $redirectUrl,
                            $fees
                        );
                } catch (PaymentInvalidResponseException $e) {
                    $this->addErrorMessage('Invalid response from payment service. Please try again');
                    return $this->redirectToList();
                }

                $view = new ViewModel(
                    [
                        'gateway' => $response['gateway_url'],
                        // we bundle the data up in such a way that the view doesn't have
                        // to know what keys/values the gateway expects; it'll just loop
                        // through this array and insert the data as hidden fields
                        'data' => [
                            'receipt_reference' => $response['receipt_reference']
                        ]
                    ]
                );
                $view->setTemplate('cpms/payment');
                return $this->renderView($view);

            case FeePaymentEntityService::METHOD_CASH:
                $result = $this->getServiceLocator()
                    ->get('Cpms\FeePayment')
                    ->recordCashPayment(
                        $fee,
                        $customerReference,
                        $amount,
                        $details['receiptDate'],
                        $details['payer'],
                        $details['slipNo']
                    );
                break;

            case FeePaymentEntityService::METHOD_CHEQUE:
                $amount = number_format($details['received'], 2);
                $result = $this->getServiceLocator()
                    ->get('Cpms\FeePayment')
                    ->recordChequePayment(
                        $fee,
                        $customerReference,
                        $amount,
                        $details['receiptDate'],
                        $details['payer'],
                        $details['slipNo'],
                        $details['chequeNo']
                    );
                break;

            case FeePaymentEntityService::METHOD_POSTAL_ORDER:
                $result = $this->getServiceLocator()
                    ->get('Cpms\FeePayment')
                    ->recordPostalOrderPayment(
                        $fee,
                        $customerReference,
                        $amount,
                        $details['receiptDate'],
                        $details['payer'],
                        $details['slipNo'],
                        $details['poNo']
                    );
                break;

            default:
                throw new PaymentInvalidTypeException("Payment type '$paymentType' is not yet implemented");
        }

        if ($result === true) {
            $this->addSuccessMessage('The fee has been paid successfully');
        } else {
            $this->addErrorMessage('The fee has NOT been paid. Please try again');
        }
        return $this->redirectToList();
    }

    /**
     * Handle response from third-party payment gateway
     */
    public function paymentResultAction()
    {
        $data = (array)$this->getRequest()->getQuery();
        return $this->resolvePayment($data);
    }

    public function resolvePayment($data)
    {
        try {
            $resultStatus = $this->getServiceLocator()
                ->get('Cpms\FeePayment')
                ->handleResponse(
                    $data,
                    FeePaymentEntityService::METHOD_CARD_OFFLINE
                );

        } catch (PaymentNotFoundException $ex) {

            $this->addErrorMessage('CPMS reference does not match valid payment record');
            return $this->redirectToList();

        } catch (PaymentInvalidStatusException $ex) {

            $this->addErrorMessage('Invalid payment state');
            return $this->redirectToList();

        }

        switch ($resultStatus) {
            case PaymentEntityService::STATUS_PAID:
                $this->addSuccessMessage('The fee(s) have been paid successfully');
                break;

            case PaymentEntityService::STATUS_FAILED:
                $this->addErrorMessage('The fee payment failed');
                break;

            case PaymentEntityService::STATUS_CANCELLED:
                // no-op, don't want a flash message
                break;
            default:
                $this->addErrorMessage('An unexpected error occured');
                break;
        }

        return $this->redirectToList();
    }

    /**
     * Helper to retrieve fee objects from parameters
     */
    private function getFeesFromParams()
    {
        $ids = explode(',', $this->params('fee'));

        $fees = [];

        foreach ($ids as $id) {
            $fees[] = $this->getServiceLocator()
                ->get('Entity\Fee')
                ->getOverview($id);
        }

        return $fees;
    }
}
