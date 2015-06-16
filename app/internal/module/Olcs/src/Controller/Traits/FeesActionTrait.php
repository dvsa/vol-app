<?php

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
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
use Dvsa\Olcs\Transfer\Query\Payment\Payment as PaymentByIdQry;
use Dvsa\Olcs\Transfer\Command\Fee\UpdateFee as UpdateFeeCmd;
use Dvsa\Olcs\Transfer\Command\Payment\CompletePayment as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Payment\PayOutstandingFees as PayOutstandingFeesCmd;

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
     */
    protected function commonPayFeesAction()
    {

        $feeIds = explode(',', $this->params('fee'));
        $fees = $this->getFees(['ids' => $feeIds,])['results'];
        $maxAmount = 0;

        foreach ($fees as $fee) {
            // bail early if any of the fees prove to be the wrong status
            if ($fee['feeStatus']['id'] !== 'lfs_ot') { // @TODO constant?
                $this->addErrorMessage('You can only pay outstanding fees');
                return $this->redirectToList();
            }
            $maxAmount += $fee['amount'];
        }

        $form = $this->getForm('FeePayment');

        // default the receipt date to 'today'
        $today = $this->getServiceLocator()->get('Helper\Date')->getDateObject();
        $form->get('details')
            ->get('receiptDate')
            ->setValue($today);

        // add the fee amount and validator to the form
        $form->get('details')
            ->get('maxAmount')
            ->setValue('£' . number_format($maxAmount, 2));

        // conditional validation needs a numeric value to compare
        $form->get('details')
            ->get('feeAmountForValidator')
            ->setValue($maxAmount);

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

            if ($this->isCardPayment($data)) {
                // remove field and validator if this is a card payment
                $this->getServiceLocator()
                    ->get('Helper\Form')
                    ->remove($form, 'details->received');
            }

            $form->setData($data);

            if ($form->isValid()) {
                return $this->initiatePaymentRequest($feeIds, $data['details']);
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
                'status' => 'lfs_wr', // @TODO constant
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
                'status' => 'lfs_ot', // @TODO constant
            ]
        );

        return $this->updateFeeAndRedirectToList($dto, 'The fee waive recommendation has been rejected');
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
                'waiveReason' => $data['fee-details']['waiveReason'],
                'paymentMethod' => 'fpm_waive',
                'status' => 'lfs_w', // @TODO constant
            ]
        );

        return $this->updateFeeAndRedirectToList($dto, 'The selected fee has been waived');
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

        if ($response->isOk() && $message) {
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
     * Kick off the CPMS payment process for a given amount
     * relating to a given array of fees
     *
     * @param array  $feeIds
     * @param array  $details
     */
    private function initiatePaymentRequest($feeIds, $details)
    {
        $paymentMethod = $details['paymentType'];

        switch ($paymentMethod) {
            case FeePaymentEntityService::METHOD_CARD_OFFLINE:

                $cpmsRedirectUrl = $this->url()->fromRoute(
                    $this->getFeesRoute() . '/fee_action',
                    ['action' => 'payment-result'],
                    ['force_canonical' => true],
                    true
                );

                $dtoData = compact('cpmsRedirectUrl', 'feeIds', 'paymentMethod');
                $dto = PayOutstandingFeesCmd::create($dtoData);
                $response = $this->handleCommand($dto);

                // Look up the new payment in order to get the redirect data
                $paymentId = $response->getResult()['id']['payment'];
                $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
                $payment = $response->getResult();
                $view = new ViewModel(
                    [
                        'gateway' => $payment['gatewayUrl'],
                        'data' => [
                            'receipt_reference' => $payment['guid']
                        ]
                    ]
                );
                $view->setTemplate('cpms/payment');
                return $this->renderView($view);

            case FeePaymentEntityService::METHOD_CASH:
                $dtoData = [
                    'feeIds' => $feeIds,
                    'paymentMethod' => $paymentMethod,
                    'received' => $details['received'],
                    'receiptDate' => $details['receiptDate'],
                    'payer' => $details['payer'],
                    'slipNo' => $details['slipNo'],
                ];
                break;

            case FeePaymentEntityService::METHOD_CHEQUE:
                $dtoData = [
                    'feeIds' => $feeIds,
                    'paymentMethod' => $paymentMethod,
                    'received' => $details['received'],
                    'receiptDate' => $details['receiptDate'],
                    'payer' => $details['payer'],
                    'slipNo' => $details['slipNo'],
                    'chequeNo' => $details['chequeNo'],
                    'chequeDate' => $details['chequeDate'],
                ];
                break;

            case FeePaymentEntityService::METHOD_POSTAL_ORDER:
                $dtoData = [
                    'feeIds' => $feeIds,
                    'paymentMethod' => $paymentMethod,
                    'received' => $details['received'],
                    'receiptDate' => $details['receiptDate'],
                    'payer' => $details['payer'],
                    'slipNo' => $details['slipNo'],
                    'poNo' => $details['poNo'],
                ];
                break;

            default:
                throw new \UnexpectedValueException("Payment type '$paymentType' is not valid");
        }

        $dto = PayOutstandingFeesCmd::create($dtoData);
        $response = $this->handleCommand($dto);

        if ($response->isOk()) {
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
        $queryStringData = (array)$this->getRequest()->getQuery();

        $dtoData = [
            'reference' => $queryStringData['receipt_reference'],
            'cpmsData' => $queryStringData,
            'paymentMethod' => 'fpm_card_offline', // @TODO constant
        ];

        $response = $this->handleCommand(CompletePaymentCmd::create($dtoData));

        if (!$response->isOk()) {
            $this->addErrorMessage('The fee payment failed');
            return $this->redirectToList();
        }

        // check payment status and redirect accordingly
        $paymentId = $response->getResult()['id']['payment'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
        $payment = $response->getResult();

        switch ($payment['status']['id']) {
            case 'pay_s_pd': // @TODO constant
                $this->addSuccessMessage('The fee(s) have been paid successfully');
                break;
            case 'pay_s_cn': // @TODO constant
                $this->addWarningMessage('The fee payment was cancelled');
                break;
            case 'pay_s_fail': // @TODO constant
                $this->addErrorMessage('The fee payment failed');
                break;
            default:
                $this->addErrorMessage('An unexpected error occured');
                break;
        }

        return $this->redirectToList();
    }

    /**
     * Create fee
     * @TODO migrate business service to new backend
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

    /**
     * Determine if we're making a card payment
     *
     * @param array $data payment data
     */
    public function isCardPayment($data)
    {
        return (
            isset($data['details']['paymentType'])
            &&
            in_array(
                $data['details']['paymentType'],
                ['fpm_card_offline', 'fpm_card_online']
                // @TODO constants
            )
        );

    }

}
