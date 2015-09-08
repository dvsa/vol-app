<?php

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Traits;

use Zend\View\Model\ViewModel;
use Common\Form\Elements\Validators\FeeAmountValidator;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Fee\Fee as FeeQry;
use Dvsa\Olcs\Transfer\Query\Fee\FeeList as FeeListQry;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Dvsa\Olcs\Transfer\Command\Fee\ApproveWaive as ApproveWaiveCmd;
use Dvsa\Olcs\Transfer\Command\Fee\RecommendWaive as RecommendWaiveCmd;
use Dvsa\Olcs\Transfer\Command\Fee\RejectWaive as RejectWaiveCmd;
use Dvsa\Olcs\Transfer\Command\Fee\CreateMiscellaneousFee as CreateFeeCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees as PayOutstandingFeesCmd;

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
                'filterForm'  => $this->getFeeFilterForm($filters)
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
     * @return \Zend\Form\Form
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
     * @return \Common\Service\Table\TableBuilder;
     */
    protected function getFeesTable($status)
    {
        $params = array_merge(
            $this->getFeesTableParams(),
            [
                'page'    => $this->params()->fromQuery('page', 1),
                'sort'    => $this->params()->fromQuery('sort', 'id'),
                'order'   => $this->params()->fromQuery('order', 'ASC'),
                'limit'   => $this->params()->fromQuery('limit', 10)
            ]
        );

        if ($status) {
            $params['status'] = $status;
        }

        $results = $this->getFees($params);

        $tableParams = array_merge($params, ['query' => $this->getRequest()->getQuery()]);
        $table = $this->getTable('fees', $results, $tableParams);

        return $this->alterFeeTable($table, $results);
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

        $form = $this->alterFeeForm($this->getForm('fee'), $fee);
        $form = $this->setDataFeeForm($fee, $form);
        $this->processForm($form);

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }

        $feeTransactions = array_filter(
            $fee['feeTransactions'],
            function ($feeTransaction) {
                // @TODO confirm AC - not sure about hiding non-complete transactions?
                // return $feeTransaction['transaction']['status']['id'] === RefData::TRANSACTION_STATUS_COMPLETE;
                return true;
            }
        );
        $table = $this->getTable('fee-transactions', $feeTransactions, []);

        $viewParams = [
            'form' => $form,
            'table' => $table,
            'invoiceNo' => $fee['id'],
            'description' => $fee['description'],
            'amount' => $fee['amount'],
            'created' => $fee['invoicedDate'],
            'outstanding' => $fee['outstanding'],
            'status' => isset($fee['feeStatus']['description']) ? $fee['feeStatus']['description'] : '',
            'fee' => $fee,
        ];

        $this->loadScripts(['forms/fee-details']);

        $view = new ViewModel($viewParams);
        $view->setTemplate('pages/fee-details.phtml');

        return $this->renderLayout($view, 'No # ' . $fee['id']);
    }

    /**
     * Common logic when handling payFeesAction
     */
    protected function commonPayFeesAction()
    {
        $feeIds = explode(',', $this->params('fee'));
        $fees = $this->getFees(['ids' => $feeIds])['results'];
        $maxAmount = 0;

        foreach ($fees as $fee) {
            // bail early if any of the fees prove to be the wrong status
            if ($fee['feeStatus']['id'] !== RefData::FEE_STATUS_OUTSTANDING) {
                $this->addErrorMessage('You can only pay outstanding fees');
                return $this->redirectToList();
            }
            $maxAmount += $fee['outstanding'];
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
                return $this->initiatePaymentRequest($feeIds, $form->getData()['details']);
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
     * @param \Zend\Form\Form $form
     * @param array $fee
     * @return \Zend\Form\Form
     */
    protected function alterFeeForm($form, $fee)
    {
        $status = $fee['feeStatus']['id'];

        if ($status !== RefData::FEE_STATUS_OUTSTANDING) {
            $form->get('form-actions')->remove('approve');
            $form->get('form-actions')->remove('reject');
            $form->get('form-actions')->remove('recommend');
            // don't remove whole fieldset as we need to keep 'back' button

            $form->get('fee-details')->remove('waiveRemainder'); // checkbox
            $form->get('fee-details')->remove('waiveReason'); // textbox

            return $form;
        }

        if ($fee['hasOutstandingWaiveTransaction']) {
            $form->get('fee-details')->remove('waiveRemainder');
            $form->get('form-actions')->remove('recommend');
        } else {
            $form->get('form-actions')->remove('approve');
            $form->get('form-actions')->remove('reject');
        }

        return $form;
    }

    /**
     * @param Table $table
     * @param array $results
     * @return Table
     */
    protected function alterFeeTable($table, $results)
    {
        // remove the 'new' action by default
        $table->removeAction('new');

        // disable 'pay' button if appropriate
        if ($results['extra']['allowFeePayments'] == false) {
            $table->disableAction('pay');
        }

        return $table;
    }


    /**
     * Process form
     *
     * @param \Zend\Form\Form $form
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
        $dto = RecommendWaiveCmd::create(
            [
                'id' => $data['fee-details']['id'],
                'version' => $data['fee-details']['version'],
                'waiveReason' => $data['fee-details']['waiveReason'],
            ]
        );

        $this->updateFeeAndRedirectToList($dto, 'Waive recommended');
    }

    /**
     * Reject waive
     *
     * @param array $data
     */
    protected function rejectWaive($data)
    {
        $dto = RejectWaiveCmd::create(
            [
                'id' => $data['fee-details']['id'],
                'version' => $data['fee-details']['version'],
            ]
        );

        return $this->updateFeeAndRedirectToList($dto, 'Waive rejected');
    }

    /**
     * Approve waive
     *
     * @param array $data
     */
    protected function approveWaive($data)
    {
        $dto = ApproveWaiveCmd::create(
            [
                'id' => $data['fee-details']['id'],
                'version' => $data['fee-details']['version'],
                'waiveReason' => $data['fee-details']['waiveReason'],
            ]
        );

        return $this->updateFeeAndRedirectToList($dto, 'Waive approved');
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
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function setDataFeeForm($fee, $form)
    {
        if ($form) {
            $form->get('fee-details')->get('id')->setValue($fee['id']);
            $form->get('fee-details')->get('version')->setValue($fee['version']);
            if (isset($fee['waiveReason'])) {
                $form->get('fee-details')->get('waiveReason')->setValue($fee['waiveReason']);
            }
        }
        return $form;
    }

    /**
     * Redirect back to list of fees
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
            case RefData::FEE_PAYMENT_METHOD_CARD_OFFLINE:

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
                $transactionId = $response->getResult()['id']['transaction'];
                $response = $this->handleQuery(PaymentByIdQry::create(['id' => $transactionId]));
                $transaction = $response->getResult();
                $view = new ViewModel(
                    [
                        'gateway' => $transaction['gatewayUrl'],
                        'data' => [
                            'receipt_reference' => $transaction['reference']
                        ]
                    ]
                );
                // render the gateway redirect and return early
                $view->setTemplate('cpms/payment');
                return $this->renderView($view);

            case RefData::FEE_PAYMENT_METHOD_CASH:
                $dtoData = [
                    'feeIds' => $feeIds,
                    'paymentMethod' => $paymentMethod,
                    'received' => $details['received'],
                    'receiptDate' => $details['receiptDate'],
                    'payer' => $details['payer'],
                    'slipNo' => $details['slipNo'],
                ];
                break;

            case RefData::FEE_PAYMENT_METHOD_CHEQUE:
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

            case RefData::FEE_PAYMENT_METHOD_POSTAL_ORDER:
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
                throw new \UnexpectedValueException("Payment type '$paymentMethod' is not valid");
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
            'paymentMethod' => RefData::FEE_PAYMENT_METHOD_CARD_OFFLINE,
        ];

        $response = $this->handleCommand(CompletePaymentCmd::create($dtoData));

        if (!$response->isOk()) {
            $this->addErrorMessage('The fee payment failed');
            return $this->redirectToList();
        }

        // check payment status and redirect accordingly
        $transactionId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $transactionId]));
        $transaction = $response->getResult();

        switch ($transaction['status']['id']) {
            case RefData::TRANSACTION_STATUS_COMPLETE:
                $this->addSuccessMessage('The fee(s) have been paid successfully');
                break;
            case RefData::TRANSACTION_STATUS_CANCELLED:
                $this->addWarningMessage('The fee payment was cancelled');
                break;
            case RefData::TRANSACTION_STATUS_FAILED:
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
     *
     * @param array $data
     */
    protected function createFee($data)
    {
        $dtoData = [
            'user' => $this->getLoggedInUser(),
            'invoicedDate' => $data['fee-details']['createdDate'],
            'feeType' => $data['fee-details']['feeType'],
            'amount' => $data['fee-details']['amount'],
        ];

        $dto = CreateFeeCmd::create($dtoData);

        $response = $this->handleCommand($dto);

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
     * @return boolean
     */
    public function isCardPayment($data)
    {
        return (
            isset($data['details']['paymentType'])
            && $data['details']['paymentType'] == RefData::FEE_PAYMENT_METHOD_CARD_OFFLINE
        );
    }
}
