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
use Common\Form\Elements\Validators\FeeAmountValidator;
use Common\Service\Cpms as CpmsService;
use Dvsa\Olcs\Transfer\Query\Fee\Fee as FeeQry;
use Dvsa\Olcs\Transfer\Query\Fee\FeeList as FeeListQry;
use Dvsa\Olcs\Transfer\Command\Fee\UpdateFee as UpdateFeeCmd;

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
    public function feesAction($template = 'layout/fees-list')
    {
        $response = $this->checkActionRedirect();
        if ($response) {
            return $response;
        }

        return $this->commonFeesAction($template);
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
     * Pay Fees Action
     */
    public function addFeeAction()
    {
        $form = $this->getForm('create-fee');

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToList();
            }
            $this->formPost($form, 'createFee');
        }

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }

        $this->getServiceLocator()->get('Helper\Form')
            ->setDefaultDate($form->get('fee-details')->get('createdDate'));

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        // currently only one route to create fees so we don't need to pass the
        // title in to this method
        $title = 'fees.create.title';

        return $this->renderView($view, $title);
    }

    /**
     * Common logic when rendering the list of fees
     */
    protected function commonFeesAction($template = 'layout/fees-list')
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
        $view->setTemplate($template);
        return $this->renderLayout($view);
    }

    protected function checkActionRedirect()
    {
        if ($this->getRequest()->isPost()) {

            $data = (array)$this->getRequest()->getPost();

            $action = isset($data['action']) ? strtolower($data['action']) : '';
            switch ($action) {
                case 'new':
                    $params = [
                        'action' => 'add-fee',
                    ];
                    break;
                case 'pay':
                default:
                    if (!isset($data['id']) || empty($data['id'])) {
                        $this->addErrorMessage('fees.pay.error.please-select');
                        return $this->redirectToList();
                    }
                    $params = [
                        'action' => 'pay-fees',
                        'fee' => implode(',', $data['id']),
                    ];
                    break;
            }

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
        $params = array_merge(
            $this->getFeesTableParams(),
            [
                'page'    => $this->params()->fromQuery('page', 1),
                'sort'    => $this->params()->fromQuery('sort', 'receivedDate'),
                'order'   => $this->params()->fromQuery('order', 'DESC'),
                'limit'   => $this->params()->fromQuery('limit', 10)
            ]
        );

        if ($status) {
            $params['status'] = $status;
        }

        $results = $this->getFees($params);

        $tableParams = array_merge($params, ['query' => $this->getRequest()->getQuery()]);
        $table = $this->getTable('fees', $results, $tableParams);

        return $this->alterFeeTable($table);
    }

    protected function getFees($params)
    {
        $query = FeeListQry::create($params);
        $response = $this->handleQuery($query);
        return $response->getResult();
    }

    protected function getFee($id)
    {
        $query = FeeQry::create(['id' => $id]);
        $response = $this->handleQuery($query);
        return $response->getResult();
    }

    /**
     * Display fee info and edit waive note
     */
    public function editFeeAction()
    {
        $id = $this->params()->fromRoute('fee', null);

        $fee = $this->getFee($id);

        $form = null;

        if ($fee['allowEdit'] == true) {
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
                case 'fpm_cheque': // @todo put these as constants somewhere
                    $viewParams['chequeNo'] = $fee['chequePoNumber'];
                    break;
                case 'fpm_po':
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
     *
     * @TODO migrate this
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
                try {
                    $service->resolveOutstandingPayments($fee);
                    $outstandingPaymentsResolved = true;
                } catch (CpmsService\Exception $ex) {
                    $this->addErrorMessage(
                        'The fee(s) selected have pending payments that cannot '
                        . 'be resolved. Please contact your adminstrator.'
                    );
                    return $this->redirectToList();
                }
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

    protected function alterFeeTable($table)
    {
        // remove the 'new' action by default
        $table->removeAction('new');
        return $table;
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
        $dto = UpdateFeeCmd::create(
            [
                'id' => $data['fee-details']['id'],
                'version' => $data['fee-details']['version'],
                'waiveReason' => $data['fee-details']['waiveReason'],
                'status' => 'lfs_wr',
            ]
        );
        $this->updateFeeAndRedirectToList($dto);
    }

    /**
     * Reject waive
     *
     * @param array $data
     */
    protected function rejectWaive($data)
    {
        $dto = UpdateFeeCmd::create(
            [
                'id' => $data['fee-details']['id'],
                'version' => $data['fee-details']['version'],
                'status' => 'lfs_ot',
            ]
        );
        $message = 'The fee waive recommendation has been rejected';
        $this->updateFeeAndRedirectToList($dto, $message);
    }

    /**
     * Approve waive
     *
     * @param array $data
     */
    protected function approveWaive($data)
    {
        $dto = UpdateFeeCmd::create(
            [
                'id' => $data['fee-details']['id'],
                'version' => $data['fee-details']['version'],
                'status' => 'lfs_ot',
                'waiveReason' => $data['fee-details']['waiveReason'],
                'paymentMethod' => 'fpm_waive',
                'feeStatus' => 'lfs_w',
            ]
        );

        $response = $this->handleCommand($dto);

        $this->addSuccessMessage('The selected fee has been waived');

        // @TODO move this to backend
        // $this->getServiceLocator()->get('Listener\Fee')->trigger(
        //     $data['fee-details']['id'],
        //     FeeListenerService::EVENT_WAIVE
        // );

        $this->redirectToList();
    }

    /**
     * Update fee and redirect to list
     *
     * @param CommandInterface $command
     * @param string $message
     */
    protected function updateFeeAndRedirectToList($command, $message = '')
    {
        $response = $this->handleCommand($command);

        if (!$response->isOk()) {
            // @TODO
        }

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

        $reference = 'Miscellaneous'; // default value

        foreach ($fees as $fee) {
            if (empty($fee) || empty($fee['id'])) {
                continue;
            }
            $organisation = $this->getServiceLocator()
                ->get('Entity\Fee')
                ->getOrganisation($fee['id']);

            if (!empty($organisation) && !empty($organisation['id'])) {
                $reference = $organisation['id'];
                break;
            }
        }

        return $reference;
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
            throw new \UnexpectedValueException($paymentType . ' is not a recognised payment type');
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
                            $fees,
                            $paymentType
                        );
                } catch (CpmsService\Exception\PaymentInvalidResponseException $e) {
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
                        $fees,
                        $customerReference,
                        $details['received'],
                        $details['receiptDate'],
                        $details['payer'],
                        $details['slipNo']
                    );
                break;

            case FeePaymentEntityService::METHOD_CHEQUE:
                $result = $this->getServiceLocator()
                    ->get('Cpms\FeePayment')
                    ->recordChequePayment(
                        $fees,
                        $customerReference,
                        $details['received'],
                        $details['receiptDate'],
                        $details['payer'],
                        $details['slipNo'],
                        $details['chequeNo'],
                        $details['chequeDate']
                    );
                break;

            case FeePaymentEntityService::METHOD_POSTAL_ORDER:
                $result = $this->getServiceLocator()
                    ->get('Cpms\FeePayment')
                    ->recordPostalOrderPayment(
                        $fees,
                        $customerReference,
                        $details['received'],
                        $details['receiptDate'],
                        $details['payer'],
                        $details['slipNo'],
                        $details['poNo']
                    );
                break;

            default:
                throw new \UnexpectedValueException("Payment type '$paymentType' is not valid");
        }

        if ($result === true) {
            $this->addSuccessMessage('The fee(s) have been paid successfully');
        } else {
            $this->addErrorMessage('The fee(s) have NOT been paid. Please try again');
        }
        return $this->redirectToList();
    }

    /**
     * Handle response from third-party payment gateway
     */
    public function paymentResultAction()
    {
        try {
            $resultStatus = $this->getServiceLocator()
                ->get('Cpms\FeePayment')
                ->handleResponse(
                    (array)$this->getRequest()->getQuery(),
                    FeePaymentEntityService::METHOD_CARD_OFFLINE
                );

        } catch (CpmsService\Exception $ex) {

            if ($ex instanceof CpmsService\Exception\PaymentNotFoundException) {
                $reason = 'CPMS reference does not match valid payment record';
            } elseif ($ex instanceof CpmsService\Exception\PaymentInvalidStatusException) {
                $reason = 'Invalid payment state';
            } else {
                $reason = $ex->getMessage();
            }

            $this->addErrorMessage('The fee payment failed: ' . $reason);
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
                $this->addWarningMessage('The fee payment was cancelled');
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

    /**
     * Create fee
     *
     * @param array $data
     */
    protected function createFee($data)
    {
        $params = array_merge(
            $data,
            [
                'user' => $this->getLoggedInUser(),
            ]
        );

        $response = $this->getServiceLocator()->get('BusinessServiceManager')
            ->get('Fee')
            ->process($params);

        if ($response->isOk()) {
            $this->addSuccessMessage('fees.create.success');
        } else {
            $this->addErrorMessage('fees.create.error');
        }

        $this->redirectToList();
    }
}
