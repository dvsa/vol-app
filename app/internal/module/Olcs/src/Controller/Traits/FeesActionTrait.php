<?php

namespace Olcs\Controller\Traits;

use Common\Rbac\IdentityProvider;
use Common\Rbac\User;
use Common\RefData;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Command\Fee\ApproveWaive as ApproveWaiveCmd;
use Dvsa\Olcs\Transfer\Command\Fee\CreateFee as CreateFeeCmd;
use Dvsa\Olcs\Transfer\Command\Fee\RecommendWaive as RecommendWaiveCmd;
use Dvsa\Olcs\Transfer\Command\Fee\RefundFee as RefundFeeCmd;
use Dvsa\Olcs\Transfer\Command\Fee\RejectWaive as RejectWaiveCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees as PayOutstandingFeesCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\ReverseTransaction as ReverseTransactionCmd;
use Dvsa\Olcs\Transfer\Query\Fee\Fee as FeeQry;
use Dvsa\Olcs\Transfer\Query\Fee\FeeList as FeeListQry;
use Dvsa\Olcs\Transfer\Query\Fee\FeeType as FeeTypeQry;
use Dvsa\Olcs\Transfer\Query\Fee\FeeTypeList as FeeTypeListQry;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Laminas\Form\Form;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Identity\IdentityProviderInterface;

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
    abstract protected function getFeesRoute();

    /**
     * Defines the controller specific fees route params
     */
    abstract protected function getFeesRouteParams();

    /**
     * Defines the controller specific fees table params
     */
    abstract protected function getFeesTableParams();

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
        return $this->commonPayFeesAction();
    }

    /**
     * Pay Fees Action
     */
    public function addFeeAction()
    {
        $form = $this->getForm('CreateFee');
        $form = $this->alterCreateFeeForm($form);

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToList();
            }
            $this->formPost($form, [$this, 'createFee']);
        }

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }

        $this->formHelper
            ->setDefaultDate($form->get('fee-details')->get('createdDate'));

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        $this->loadScripts(['forms/create-fee']);

        $this->placeholder()->setPlaceholder('contentTitle', 'fees.create.title');
        return $this->renderView($view, 'fees.create.title');
    }

    /**
     * Common logic when rendering the list of fees
     */
    private function commonFeesAction()
    {
        $this->loadScripts(['forms/filter', 'table-actions']);

        $status = $this->params()->fromQuery('status');
        $table = $this->getFeesTable($status);
        $this->updateTableActionWithQuery($table);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('pages/table');
        return $this->renderLayout($view);
    }

    public function getLeftView()
    {
        $status = $this->params()->fromQuery('status');
        $filters = [
            'status' => $status
        ];

        $view = new ViewModel(['filterForm' => $this->getFeeFilterForm($filters)]);
        $view->setTemplate('sections/fees/partials/left');

        return $view;
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
                ['query' => $this->getRequest()->getQuery()->toArray()],
                true
            );
        }
    }

    /**
     * Get fee filter form
     *
     * @param array $filters Form Data
     *
     * @return \Common\Form\Form
     */
    protected function getFeeFilterForm($filters = [])
    {
        /**
 * @var \Common\Service\Helper\FormHelperService $formHelper
*/
        $formHelper = $this->formHelper;
        $form = $formHelper->createForm('FeeFilter', false);
        $form->setData($filters);

        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        return $form;
    }

    /**
     * Get fees table
     *
     * @param  string $status
     * @return \Common\Service\Table\TableBuilder;
     */
    protected function getFeesTable($status)
    {
        $params = array_merge(
            $this->getFeesTableParams(),
            [
                'page'    => $this->params()->fromQuery('page', 1),
                'sort'    => $this->params()->fromQuery('sort', 'invoicedDate'),
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
        if ($this->isButtonPressed('refund')) {
            $route = $this->getFeesRoute() . '/fee_action';
            return $this->redirect()->toRoute(
                $route,
                ['action' => 'refund-fee'],
                ['query' => $this->getRequest()->getQuery()->toArray()],
                true
            );
        }

        $id = $this->params()->fromRoute('fee', null);

        $fee = $this->getFee($id);

        $form = $this->alterFeeForm($this->getForm('Fee'), $fee);
        $this->formHelper->setFormActionFromRequest($form, $this->getRequest());
        $form = $this->setDataFeeForm($fee, $form);
        $this->processForm($form);

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }

        $table = $this->getTable('fee-transactions', $fee['displayTransactions'], []);
        $this->updateTableActionWithQuery($table);

        $viewParams = [
            'form' => $form,
            'table' => $table,
            'invoiceNo' => $fee['id'],
            'description' => $fee['description'],
            'amount' => $fee['amount'],
            'netAmount' => $fee['netAmount'],
            'vatAmount' => $fee['vatAmount'],
            'vatInfo' => $fee['vatInfo'],
            'created' => $fee['invoicedDate'],
            'outstanding' => $fee['outstanding'],
            'status' => $fee['feeStatus']['description'] ?? '',
            'fee' => $fee
        ];

        $this->placeholder()->setPlaceholder('contentTitle', 'internal.fee-details.title');

        $this->loadScripts(['forms/fee-details']);

        $view = new ViewModel($viewParams);
        $view->setTemplate('sections/fees/pages/fee-details');

        $layout = $this->renderLayout($view, 'internal.fee-details.title');

        $this->maybeClearLeft($layout);

        return $layout;
    }

    /**
     * Alter which feeTransactions are displayed in the table,
     * called as array_filter callback
     *
     * @param  array $feeTransaction
     * @return boolean
     */
    public function ftDisplayFilter($feeTransaction)
    {
        // OLCS-10687 exclude outstanding waive transactions
        if (
            $feeTransaction['transaction']['status']['id'] === RefData::TRANSACTION_STATUS_OUTSTANDING
            && $feeTransaction['transaction']['type']['id'] === RefData::TRANSACTION_TYPE_WAIVE
        ) {
            return false;
        }

        return true;
    }

    protected function maybeClearLeft($layout)
    {
        $layout->clearLeft();
    }

    /**
     * Redirect back to fee details page
     */
    protected function redirectToFeeDetails($ajax = false)
    {
        $method = $ajax ? 'toRouteAjax' : 'toRoute';

        $route = $this->getFeesRoute() . '/fee_action';
        return $this->redirect()->$method(
            $route,
            ['action' => 'edit-fee'],
            ['query' => $this->getRequest()->getQuery()->toArray()],
            true
        );
    }

    /**
     * Redirect back to transaction details page
     */
    protected function redirectToTransaction($ajax = false, $transactionId = null)
    {
        $method = $ajax ? 'toRouteAjax' : 'toRoute';
        $route = $this->getFeesRoute() . '/fee_action/transaction';
        $params = ['action' => 'edit-fee'];

        if ($transactionId) {
            $params['transaction'] = $transactionId;
        }

        return $this->redirect()->$method(
            $route,
            $params,
            ['query' => $this->getRequest()->getQuery()->toArray()],
            true
        );
    }

    /**
     * Display transaction info
     */
    public function transactionAction()
    {
        $id = $this->params()->fromRoute('transaction', null);

        $query = PaymentByIdQry::create(['id' => $id]);
        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            $this->addErrorMessage('unknown-error');
            return $this->redirectToFeeDetails();
        }

        $transaction = $response->getResult();

        $fees = $transaction['fees'];

        $table = $this->getTable('transaction-fees', $fees);
        $this->updateTableActionWithQuery($table);

        $urlHelper = $this->urlHelper;

        $backLink = $urlHelper->fromRoute(
            $this->getFeesRoute() . '/fee_action',
            ['action' => 'edit-fee'],
            ['query' => $this->getRequest()->getQuery()->toArray()],
            true
        );

        $receiptLink = '';

        switch ($transaction['type']['id']) {
            case RefData::TRANSACTION_TYPE_PAYMENT:
                $title = 'internal.transaction-details.title-payment';
                if (
                    $transaction['status']['id'] == RefData::TRANSACTION_STATUS_COMPLETE
                    && !empty($transaction['reference'])
                ) {
                    $receiptLink = $urlHelper->fromRoute(
                        $this->getFeesRoute() . '/print-receipt',
                        ['reference' => $transaction['reference']],
                        [],
                        true
                    );
                }
                break;
            case RefData::TRANSACTION_TYPE_REVERSAL:
                $title = 'internal.transaction-details.title-reversal';
                break;
            default:
                $title = 'internal.transaction-details.title-other';
                break;
        }

        $viewParams = [
            'table' => $table,
            'transaction' => $transaction,
            'backLink' => $backLink,
            'receiptLink' => $receiptLink,
            'reverseLink' => $this->getReverseLink($transaction),
        ];

        $this->placeholder()->setPlaceholder('contentTitle', $title);

        $view = new ViewModel($viewParams);
        $view->setTemplate('sections/fees/pages/transaction-details');

        $layout = $this->renderLayout($view, $title);

        $this->maybeClearLeft($layout);

        return $layout;
    }

    /**
     * Determine reversal url from transaction data
     *
     * @return string
     */
    protected function getReverseLink(array $transaction)
    {
        if ($transaction['displayReversalOption']) {
            return $this->urlHelper->fromRoute(
                $this->getFeesRoute() . '/fee_action/transaction/reverse',
                ['transaction' => $transaction['id']],
                ['query' => $this->getRequest()->getQuery()->toArray()],
                true
            );
        }

        return '';
    }

    /**
     * Common logic when handling payFeesAction
     *
     * @return mixed
     */
    protected function commonPayFeesAction()
    {
        $feeIds = explode(',', $this->params('fee'));

        $dtoData = [
            'feeIds' => $feeIds,
            'shouldResolveOnly' => true
        ];
        $response = $this->handleCommand(PayOutstandingFeesCmd::create($dtoData));

        if (!$response->isOk()) {
            $this->addErrorMessage('unknown-error');
            return $this->redirectToList();
        }

        $errorMessage = $this->getResolvingErrorMessage($response->getResult()['messages']);
        if ($errorMessage !== '') {
            $this->addErrorMessage($errorMessage);
            return $this->redirectToList();
        }

        $feeData = $this->getFees(['ids' => $feeIds]);
        $fees = $feeData['results'];
        $title = 'Pay fee' . (count($fees) !== 1 ? 's' : '');
        $backToFee = !empty($this->params()->fromQuery('backToFee'));

        foreach ($fees as $fee) {
            // bail early if any of the fees prove to be the wrong status
            if ($fee['feeStatus']['id'] !== RefData::FEE_STATUS_OUTSTANDING) {
                $this->addErrorMessage('fee.not-outstanding.error');
                return $this->redirectToList();
            }

            if ($fee['ruleDateBeforeInvoice'] && !$fee['isAccrualBeforeInvoiceDatePermitted']) {
                $this->addErrorMessage('fee.rule-before-invoiced-date.error');
                return $this->redirectToList();
            }
        }

        $form = $this->getForm('FeePayment');
        $form = $this->alterPaymentForm($form, $feeData, $backToFee);

        $this->loadScripts(['forms/fee-payment']);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array) $request->getPost();
            $form->setData($data);
        }

        $hasProcessed = false;
        if ($form->has('address')) {
            $hasProcessed =
                $this->formHelper->processAddressLookupForm($form, $this->getRequest());
        }

        if (!$hasProcessed && $request->isPost()) {
            $data = (array) $request->getPost();

            // check if we need to recover serialized data from confirm step
            if ($this->isButtonPressed('confirm') && isset($data['custom'])) {
                $data = json_decode($data['custom'], true);
            }

            if ($this->isCardPayment($data)) {
                // remove field and validator if this is a card payment
                $this->formHelper
                    ->remove($form, 'details->received');
            }

            $form->setData($data);

            if ($form->isValid()) {
                if ($this->shouldConfirmPayment($feeData, $data)) {
                    $confirmMessage = $this->getConfirmPaymentMessage($feeData, $data);
                    $confirm = $this->confirm($confirmMessage, false, json_encode($data));

                    if ($confirm instanceof ViewModel) {
                        return $this->renderView($confirm);
                    }
                }

                $formData = $form->getData();
                $address = $formData['address'] ?? null;
                return $this->initiatePaymentRequest(
                    $feeIds,
                    $formData['details'],
                    $backToFee,
                    $address
                );
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        $this->placeholder()->setPlaceholder('contentTitle', $title);
        return $this->renderView($view, $title);
    }

    /**
     * Refund fee action
     *
     * @return mixed
     */
    public function refundFeeAction()
    {
        /**
 * @var IdentityProviderInterface $authenticationService
*/
        $authenticationService = $this->identityProvider;
        /**
 * @var User $user
*/
        $user = $authenticationService->getIdentity();
        $isAdmin = $user->hasRole(RefData::ROLE_INTERNAL_ADMIN);

        $form = $this->getRefundFeeForm($isAdmin);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array) $this->getRequest()->getPost();
            $form->setData($data);
        }
        $hasProcessed = false;
        if (!$this->isMiscellaneousFees() && $isAdmin) {
            $this->alterRefundFeeFormForAdmin($form);
        }
        if ($form->has('address')) {
            $hasProcessed =
                $this->formHelper->processAddressLookupForm($form, $this->getRequest());
        }

        if (!$hasProcessed && $this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToFeeDetails();
            }
            if ($form->isValid()) {
                $dtoData = $this->refundFeeDtoData($data);
                /**
                 * @var Response $response
                 */
                $response = $this->handleCommand(
                    RefundFeeCmd::create($dtoData)
                );
                if ($response->isOk()) {
                    $this->addSuccessMessage('fees.refund.success');
                } else {
                    $this->processErrorResponse($response);
                }
                return $this->redirectToFeeDetails(true);
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        $this->placeholder()->setPlaceholder('contentTitle', 'fees.refund.title');
        return $this->renderView($view, 'fees.refund.title');
    }

    private function processErrorResponse(Response $response): void
    {
        try {
            $responseContent = json_decode($response->getBody());
            $message = implode('; ', $responseContent->messages);
            $error = strlen($message) > 0 ? $message : 'unknown-error';
            $this->addErrorMessage($error);
        } catch (\Exception) {
            $this->addErrorMessage('unknown-error');
        }
    }

    private function refundFeeDtoData($data)
    {
        $dtoData = [
            'id' => $this->params('fee')
        ];
        if (array_key_exists('details', $data)) {
            $details = $data['details'];
            if (array_key_exists('customerReference', $details)) {
                $dtoData['customerReference'] = $details['customerReference'];
            }
            if (array_key_exists('customerName', $details)) {
                $dtoData['customerName'] = $details['customerName'];
            }
            if (array_key_exists('address', $data)) {
                $dtoData['address'] = $data['address'];
            }
        }
        return $dtoData;
    }

    private function alterRefundFeeFormForAdmin(Form $form): void
    {
        $fee = $this->getFee($this->params('fee'));
        $form->get('details')->get('customerReference')->setValue($fee['customerReference']);
        $form->getInputFilter()->get('details')->get('customerName')->setRequired(false);
        $form->getInputFilter()->get('details')->get('customerReference')->setRequired(false);
        $form->getInputFilter()->get('address')->get('addressLine1')->setRequired(false);
        $form->getInputFilter()->get('address')->get('town')->setRequired(false);
        $form->getInputFilter()->get('address')->get('town')->setRequired(false);
        $form->getInputFilter()->get('address')->get('postcode')->setRequired(false);
        $form->getInputFilter()->get('address')->get('countryCode')->setRequired(false);
        $form->get('details')->remove('customerName');
        $form->remove('address');
    }

    /**
     * Get refund fee form
     *
     * @return Form
     */
    private function getRefundFeeForm($isAdmin)
    {
        $formName = $this->isMiscellaneousFees() || $isAdmin ? 'RefundFee' : 'GenericConfirmation';
        $form = $this->formHelper->createFormWithRequest($formName, $this->getRequest());
        if ($formName === 'GenericConfirmation') {
            $form->setSubmitLabel('Refund');
        }
        $form->get('messages')->get('message')->setValue('fees.refund.confirm');
        return $form;
    }

    /**
     * Reverse transaction action
     *
     * @return mixed
     */
    public function reverseTransactionAction()
    {
        $transactionId = $this->params('transaction');
        $form = $this->getReverseTransactionForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array) $request->getPost();
            $form->setData($data);
        }

        $hasProcessed = false;
        if ($form->has('address')) {
            $hasProcessed =
                $this->formHelper->processAddressLookupForm($form, $this->getRequest());
        }

        if (!$hasProcessed && $request->isPost()) {
            $redirect = $this->handleReverseTransactionPost($form, $transactionId);
            if (!is_null($redirect)) {
                return $redirect;
            }
        }

        $query = PaymentByIdQry::create(['id' => $transactionId]);
        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            $this->addErrorMessage('unknown-error');
            return $this->redirectToTransaction();
        }

        $transaction = $response->getResult();

        if (!$transaction['canReverse']) {
            $this->addErrorMessage('fees.reverse-transaction.cannotReverse');
            return $this->redirectToTransaction(true);
        }

        $translator = $this->translationHelper;
        $message = $translator->translateReplace(
            'fees.reverse-transaction.confirm',
            [strtolower($transaction['paymentMethod']['description'])]
        );
        $form->get('messages')->get('message')->setValue($message);

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        $this->placeholder()->setPlaceholder('contentTitle', 'fees.reverse-transaction.title');
        return $this->renderView($view, 'fees.reverse-transaction.title');
    }

    /**
     * Get reverse transaction form
     *
     * @return Form
     */
    private function getReverseTransactionForm()
    {
        $formHelper = $this->formHelper;
        $form = $formHelper->createFormWithRequest('ReverseTransaction', $this->getRequest());
        if (!$this->isMiscellaneousFees()) {
            $formHelper->remove($form, 'details->customerReference');
            $formHelper->remove($form, 'details->customerName');
            $formHelper->remove($form, 'address');
        }
        return $form;
    }

    /**
     * @param Form $form          formg
     * @param int  $transactionId transaction id
     *
     * @return mixed
     */
    private function handleReverseTransactionPost($form, $transactionId)
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToTransaction();
        }
        $data = (array) $this->getRequest()->getPost();
        $form->setData($data);
        if ($form->isValid()) {
            $formData = $form->getData();
            $formDataDetails = $formData['details'];
            $dtoData = [
                'id' => $transactionId,
                'reason' => $formDataDetails['reason']
            ];
            if ($this->isMiscellaneousFees()) {
                $dtoData['customerReference'] = $formDataDetails['customerReference'];
                $dtoData['customerName'] = $formDataDetails['customerName'];
                $dtoData['address'] = $formData['address'];
            }
            $response = $this->handleCommand(ReverseTransactionCmd::create($dtoData));
            if ($response->isOk()) {
                $this->addSuccessMessage('fees.reverse-transaction.success');
            } else {
                $result = $response->getResult();
                if (isset($result['messages'])) {
                    foreach ($result['messages'] as $error) {
                        $this->addErrorMessage($error);
                    }
                }
            }
            return $this->redirectToTransaction(true);
        }
    }

    /**
     * Alter fee form
     *
     * @param  \Laminas\Form\Form $form
     * @param  array              $fee
     * @return \Laminas\Form\Form
     */
    protected function alterFeeForm($form, $fee)
    {
        $status = $fee['feeStatus']['id'];

        if ($fee['canRefund'] === false) {
            $form->get('form-actions')->remove('refund');
        }

        if ($status !== RefData::FEE_STATUS_OUTSTANDING) {
            $form->get('form-actions')->remove('pay');
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
     * Alter fee payment form
     *
     * @param  \Laminas\Form\Form $form
     * @param  array              $feeData   from FeeList query
     * @param  boolean            $backToFee whether to populate 'backToFee' field which
     *                                       controls the final redirect
     * @return \Laminas\Form\Form
     */
    protected function alterPaymentForm($form, $feeData, $backToFee = false)
    {
        $minAmount = $feeData['extra']['minPayment'];
        $maxAmount = $feeData['extra']['totalOutstanding'];

        // default the receipt date to 'today'
        $today = $this->dateHelper->getDateObject();
        $form->get('details')
            ->get('receiptDate')
            ->setValue($today);

        // add the fee amount and validator to the form
        $form->get('details')
            ->get('maxAmount')
            ->setValue('£' . number_format($maxAmount, 2));

        // conditional validation needs numeric values to compare
        $form->get('details')
            ->get('maxAmountForValidator')
            ->setValue($maxAmount);
        $form->get('details')
            ->get('minAmountForValidator')
            ->setValue($minAmount);

        if ($backToFee) {
            // note we don't actually use the query string id here, it's safer
            // to treat it as a boolean flag and use the id from the data
            $form->get('details')->get('backToFee')->setValue($feeData['results'][0]['id']);
        }

        $formHelper = $this->formHelper;
        if (!$this->isMiscellaneousFees()) {
            $formHelper->remove($form, 'details->customerReference');
            $formHelper->remove($form, 'details->customerName');
            $formHelper->remove($form, 'address');
        }

        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Alter create fee form
     *
     * @param \Common\Form\Form $form Form
     *
     * @return \Common\Form\Form
     */
    protected function alterCreateFeeForm($form)
    {
        $formHelper = $this->formHelper;

        // disable amount validation by default
        $form->get('fee-details')->get('amount')->setAttribute('readonly', true);
        $formHelper->disableEmptyValidationOnElement($form, 'fee-details->amount');

        // remove IRFO fields by default
        $formHelper->remove($form, 'fee-details->irfoGvPermit');
        $formHelper->remove($form, 'fee-details->irfoPsvAuth');
        $formHelper->remove($form, 'fee-details->quantity');

        // populate fee type select
        $options = $this->fetchFeeTypeValueOptions();
        $form->get('fee-details')->get('feeType')->setValueOptions($options);

        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Get value options array for create fee type form
     *
     * @param  string $effectiveDate  string presentation of DateTime
     * @param  int    $currentFeeType
     * @return array
     */
    protected function fetchFeeTypeValueOptions($effectiveDate = null, $currentFeeType = null)
    {
        $data = $this->fetchFeeTypeListData($effectiveDate, $currentFeeType);

        return $data['extra']['valueOptions']['feeType'] ?? [];
    }

    /**
     * Fetch fee type list data
     *
     * @param  string $effectiveDate  string presentation of DateTime
     * @param  int    $currentFeeType
     * @return array|void
     */
    protected function fetchFeeTypeListData($effectiveDate = null, $currentFeeType = null)
    {
        $dtoData = $this->getFeeTypeDtoData();
        if ($effectiveDate) {
            $dtoData['effectiveDate'] = $effectiveDate;
        }
        if ($currentFeeType) {
            $dtoData['currentFeeType'] = $currentFeeType;
        }
        $response = $this->handleQuery(FeeTypeListQry::create($dtoData));
        if ($response->isOk()) {
            return $response->getResult();
        }
    }

    protected function getFeeTypeDtoData()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function getCreateFeeDtoData($formData)
    {
        return [];
    }

    /**
     * Alter Fee Table
     *
     * @param \Common\Service\Table\TableBuilder $table   Table object
     * @param array                              $results Data
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function alterFeeTable($table, $results)
    {
        // disable 'pay' button if appropriate
        if ($results['extra']['allowFeePayments'] == false) {
            $table->disableAction('pay');
        }

        return $table;
    }


    /**
     * Process form
     *
     * @param \Common\Form\Form $form Form
     *
     * @return void
     */
    protected function processForm($form)
    {
        if ($this->isButtonPressed('pay')) {
            $this->redirectToPay();
        } elseif ($this->isButtonPressed('recommend')) {
            $this->formPost($form, [$this, 'recommendWaive']);
        } elseif ($this->isButtonPressed('reject')) {
            $this->formPost($form, [$this, 'rejectWaive']);
        } elseif ($this->isButtonPressed('approve')) {
            $this->formPost($form, [$this, 'approveWaive']);
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
        $data = $data['validData'];
        $dto = RecommendWaiveCmd::create(
            [
                'id' => $data['fee-details']['id'],
                'version' => $data['fee-details']['version'],
                'waiveReason' => $data['fee-details']['waiveReason'],
            ]
        );

        $this->updateFeeAndRedirectToList($dto, 'Waive recommended', false);
    }

    /**
     * Reject waive
     *
     * @param array $data
     */
    protected function rejectWaive($data)
    {
        $data = $data['validData'];
        $dto = RejectWaiveCmd::create(
            [
                'id' => $data['fee-details']['id'],
                'version' => $data['fee-details']['version'],
            ]
        );

        return $this->updateFeeAndRedirectToList($dto, 'Waive rejected', false);
    }

    /**
     * Approve waive
     *
     * @param array $data
     */
    protected function approveWaive($data)
    {
        $data = $data['validData'];
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
     * Update fee and redirect to list (optional)
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface $command        command
     * @param string                                       $message        message
     * @param bool                                         $redirectToList redirect to list
     *
     * @return \Laminas\Http\Response
     */
    protected function updateFeeAndRedirectToList($command, $message = '', $redirectToList = true)
    {
        $response = $this->handleCommand($command);

        if ($response->isOk() && $message) {
            $this->addSuccessMessage($message);
        }

        if (!$response->isOk()) {
            $this->addErrorMessage('unknown-error');
        }

        return $redirectToList
            ? $this->redirectToList()
            : $this->redirect()->toRouteAjax(null, [], ['code' => '303'], true);
    }

    /**
     * Set data
     *
     * @param  array              $fee
     * @param  \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    protected function setDataFeeForm($fee, $form)
    {
        if ($form) {
            $form->get('fee-details')->get('id')->setValue($fee['id']);
            $form->get('fee-details')->get('version')->setValue($fee['version']);
            if (isset($fee['waiveReason']) && $form->get('fee-details')->has('waiveReason')) {
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
        return $this->redirect()->toRouteAjax($route, $params, ['query' => $this->getRequest()->getQuery()->toArray()]);
    }

    /**
     * Redirect to 'pay fee' page
     */
    protected function redirectToPay()
    {
        $feeId = $this->params()->fromRoute('fee', null);
        $route = $this->getFeesRoute() . '/fee_action';
        $params = ['fee' => $feeId, 'action' => 'pay-fees'];
        $options = ['query' => array_merge(['backToFee' => $feeId], $this->getRequest()->getQuery()->toArray())];
        return $this->redirect()->toRoute($route, $params, $options, true);
    }

    /**
     * Kick off the CPMS payment process for a given amount
     * relating to a given array of fees
     *
     * @param array   $feeIds    fee ids
     * @param array   $details   details
     * @param boolean $backToFee back to fee
     * @param array   $address   address
     *
     * @return \Laminas\Http\Response | ViewModel
     */
    private function initiatePaymentRequest($feeIds, $details, $backToFee, $address = null)
    {
        $paymentMethod = $details['paymentType'];

        $dtoData = [];
        if ($this->isMiscellaneousFees()) {
            $dtoData['customerReference'] = $details['customerReference'];
            $dtoData['customerName'] = $details['customerName'];
            $dtoData['address'] = $address;
        }

        switch ($paymentMethod) {
            case RefData::FEE_PAYMENT_METHOD_CARD_OFFLINE:
                $cpmsRedirectUrl = $this->url()->fromRoute(
                    $this->getFeesRoute() . '/fee_action',
                    [
                    'action' => 'payment-result',
                    ],
                    [
                    'force_canonical' => true,
                    'query' => array_merge(
                        ['backToFee' => (int) $backToFee],
                        $this->getRequest()->getQuery()->toArray()
                    ),
                    ],
                    true
                );

                $dtoData = array_merge($dtoData, compact('cpmsRedirectUrl', 'feeIds', 'paymentMethod'));
                $dto = PayOutstandingFeesCmd::create($dtoData);
                $response = $this->handleCommand($dto);

                if (!$response->isOk()) {
                    $this->addErrorMessage('unknown-error');
                    return $this->redirectToList();
                }

                $errorMessage = $this->getResolvingErrorMessage($response->getResult()['messages']);
                if ($errorMessage !== '') {
                    $this->addErrorMessage($errorMessage);
                    return $this->redirectToList();
                }

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
                $dtoData = array_merge(
                    $dtoData,
                    [
                    'feeIds' => $feeIds,
                    'paymentMethod' => $paymentMethod,
                    'received' => $details['received'],
                    'receiptDate' => $details['receiptDate'],
                    'payer' => $details['payer'],
                    'slipNo' => $details['slipNo'],
                    ]
                );
                break;

            case RefData::FEE_PAYMENT_METHOD_CHEQUE:
                $dtoData = array_merge(
                    $dtoData,
                    [
                    'feeIds' => $feeIds,
                    'paymentMethod' => $paymentMethod,
                    'received' => $details['received'],
                    'receiptDate' => $details['receiptDate'],
                    'payer' => $details['payer'],
                    'slipNo' => $details['slipNo'],
                    'chequeNo' => $details['chequeNo'],
                    'chequeDate' => $details['chequeDate'],
                    ]
                );
                break;

            case RefData::FEE_PAYMENT_METHOD_POSTAL_ORDER:
                $dtoData = array_merge(
                    $dtoData,
                    [
                    'feeIds' => $feeIds,
                    'paymentMethod' => $paymentMethod,
                    'received' => $details['received'],
                    'receiptDate' => $details['receiptDate'],
                    'payer' => $details['payer'],
                    'slipNo' => $details['slipNo'],
                    'poNo' => $details['poNo'],
                    ]
                );
                break;

            default:
                throw new \UnexpectedValueException("Payment type '$paymentMethod' is not valid");
        }

        $dto = PayOutstandingFeesCmd::create($dtoData);
        $response = $this->handleCommand($dto);

        if ($response->isOk()) {
            $this->addSuccessMessage('The payment was made successfully');
        } else {
            $this->addErrorMessage('The fee(s) have NOT been paid. Please try again');
        }
        $licenceContinued = isset($response->getResult()['flags'][RefData::RESULT_LICENCE_CONTINUED])
            && (int) $response->getResult()['flags'][RefData::RESULT_LICENCE_CONTINUED] === 1;
        if ($licenceContinued) {
            $this->addSuccessMessage('Licence has been continued');
        }

        if (isset($details['backToFee']) && !empty($details['backToFee'])) {
            return $this->redirectToFeeDetails(true);
        }

        return $this->redirectToList();
    }

    /**
     * Get resolving error message
     *
     * @param array $messages messages
     *
     * @return string
     */
    protected function getResolvingErrorMessage($messages)
    {
        $errorMessage = '';
        $translateHelper = $this->translationHelper;
        foreach ($messages as $message) {
            if (is_array($message) && array_key_exists(RefData::ERR_WAIT, $message)) {
                $errorMessage = $translateHelper->translate('payment.error.15sec');
                break;
            } elseif (is_array($message) && array_key_exists(RefData::ERR_NO_FEES, $message)) {
                $errorMessage = $translateHelper->translate('payment.error.feepaid');
                break;
            }
        }
        return $errorMessage;
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
        $licenceContinued = isset($response->getResult()['flags'][RefData::RESULT_LICENCE_CONTINUED])
            && (int) $response->getResult()['flags'][RefData::RESULT_LICENCE_CONTINUED] === 1;

        // check payment status and redirect accordingly
        $transactionId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $transactionId]));
        $transaction = $response->getResult();

        switch ($transaction['status']['id']) {
            case RefData::TRANSACTION_STATUS_COMPLETE:
                $this->addSuccessMessage('The fee(s) have been paid successfully');
                if ($licenceContinued) {
                    $this->addSuccessMessage('Licence has been continued');
                }
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

        if (isset($queryStringData['backToFee']) && !empty($queryStringData['backToFee'])) {
            return $this->redirectToFeeDetails();
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
        $form = $data['form'];
        $data = $data['validData'];

        $dtoData = $this->getCreateFeeDtoData($data);

        $dto = CreateFeeCmd::create($dtoData);

        $response = $this->handleCommand($dto);

        if ($response->isOk()) {
            $this->addSuccessMessage('fees.create.success');
            $this->redirectToList();
        } else {
            $errors = $response->getResult();
            \Olcs\Data\Mapper\CreateFee::mapFromErrors($form, $errors);
            if (!empty($errors)) {
                $this->addErrorMessage('fees.create.error');
            }
        }
    }

    /**
     * Determine if we're making a card payment
     *
     * @param  array $data payment data
     * @return boolean
     */
    public function isCardPayment($data)
    {
        return (
            isset($data['details']['paymentType'])
            && $data['details']['paymentType'] == RefData::FEE_PAYMENT_METHOD_CARD_OFFLINE
        );
    }

    /**
     * @param  array $feeData  from FeeList query
     * @return boolean
     */
    protected function shouldConfirmPayment(array $feeData, array $postData)
    {
        if ($this->isCardPayment($postData)) {
            return false;
        }

        // force the amounts to be in the same format, we can't compare floats for equality
        $received = number_format((float)$postData['details']['received'], 2);
        $total = number_format((float)$feeData['extra']['totalOutstanding'], 2);
        return ($received !== $total);
    }

    /**
     * @param  array $feeData  from FeeList query response
     * @param  array $postData
     * @return string message (translation key)
     */
    protected function getConfirmPaymentMessage(array $feeData, $postData)
    {
        $received = $postData['details']['received'];
        $total = $feeData['extra']['totalOutstanding'];

        if ($received > $total) {
            // overpayment
            if ($received > ($total * 2)) {
                // A slightly altered warning message is displayed if the payment amount
                // is more than double the amount outstanding in order to avoid mis-keying:
                return 'internal.fee-payment.over-payment-double';
            }
            return 'internal.fee-payment.over-payment-standard';
        }

        // underpayment (different message for one or multiple fees)
        $suffix = $feeData['count'] > 1 ? 'multiple' : 'single';
        return 'internal.fee-payment.part-payment-' . $suffix;
    }

    public function feeTypeAction()
    {
        $id = $this->params('id');
        $response = $this->handleQuery(FeeTypeQry::create(['id' => $id]));

        $feeType = $response->getResult();

        return new JsonModel(
            [
                'value' => $feeType['displayValue'],
                'taxRate' => $feeType['vatRate'],
                'showQuantity' => $feeType['showQuantity']
            ]
        );
    }

    public function feeTypeListAction()
    {
        $valueOptions = $this->fetchFeeTypeValueOptions($this->params('date'));

        // map to format that the JS expects :-/
        $feeTypes = array_map(
            fn($id, $description) => [
                'value' => $id,
                'label'  => $description,
            ],
            array_keys($valueOptions),
            $valueOptions
        );

        array_unshift($feeTypes, ["value" => "", "label" => ""]);

        return new JsonModel($feeTypes);
    }

    /**
     * Update table action with query
     *
     * @param \Common\Service\Table\TableBuilder $table Table
     *
     * @return void
     */
    protected function updateTableActionWithQuery($table)
    {
        $query = $this->getRequest()->getUri()->getQuery();
        if ($query) {
            $action = $table->getVariable('action') . '?' . $query;
            $table->setVariable('action', $action);
        }
    }

    /**
     * Is miscellaneous fees
     *
     * @return bool
     */
    protected function isMiscellaneousFees()
    {
        return $this->getFeesRoute() === 'admin-dashboard/admin-payment-processing/misc-fees';
    }
}
