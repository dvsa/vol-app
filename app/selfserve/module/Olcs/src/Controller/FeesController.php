<?php

/**
 * Fees Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Traits\GenericReceipt;
use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePayment;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Transfer\Query\Fee\Fee;
use Dvsa\Olcs\Transfer\Query\Organisation\OutstandingFees;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentById;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Fees Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeesController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait;
    use GenericReceipt;

    protected const PAYMENT_METHOD = RefData::FEE_PAYMENT_METHOD_CARD_ONLINE;

    private $disableCardPayments = false;

    protected TableFactory $tableFactory;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param TableFactory $tableFactory
     * @param GuidanceHelperService $guidanceHelper
     * @param ScriptFactory $scriptFactory
     * @param FormHelperService $formHelper
     * @param UrlHelperService $urlHelper
     * @param TranslationHelperService $translationHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        TableFactory $tableFactory,
        protected GuidanceHelperService $guidanceHelper,
        protected ScriptFactory $scriptFactory,
        protected FormHelperService $formHelper,
        protected UrlHelperService $urlHelper,
        protected TranslationHelperService $translationHelper
    ) {
        $this->tableFactory = $tableFactory;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Fees index action
     */
    #[\Override]
    public function indexAction()
    {
        $response = $this->checkActionRedirect();
        if ($response) {
            return $response;
        }

        $organisationId = $this->getCurrentOrganisationId();

        $fees = $this->getOutstandingFeesForOrganisation($organisationId);

        $table = $this->tableFactory
            ->buildTable('fees', $fees, [], false);

        if ($this->getDisableCardPayments()) {
            $table->removeAction('pay');
            $table->removeColumn('checkbox');
            $this->guidanceHelper->append('selfserve-card-payments-disabled');
        }

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('pages/fees/home');

        $this->scriptFactory->loadFile('dashboard-fees');

        return $view;
    }

    /**
     * Pay Fees action
     * @psalm-suppress UndefinedDocblockClass
     */
    public function payFeesAction()
    {
        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel') || $this->isButtonPressed('customCancel')) {
                return $this->redirectToIndex();
            }
            $feeIds = explode(',', (string) $this->params('fee'));
            return $this->payOutstandingFees($feeIds);
        }

        $fees = $this->getFeesFromParams();

        if (empty($fees)) {
            $this->addErrorMessage('payment.error.feepaid');
            return $this->redirectToIndex();
        }

        /* @var $form \Common\Form\Form */
        $form = $this->getForm();
        $firstFee = reset($fees);

        if (count($fees) > 1) {
            $table = $this->tableFactory
                ->buildTable('pay-fees', $fees, [], false);
            $view = new ViewModel(
                [
                    'table' => $table,
                    'form' => $form,
                    'hasContinuation' => $this->hasContinuationFee($fees),
                    'type' => 'fees'
                ]
            );
            $view->setTemplate('pages/fees/pay-multi');
        } else {
            $fee = array_shift($fees);
            $view = new ViewModel(
                [
                    'fee' => $fee,
                    'form' => $form,
                    'hasContinuation' => $fee['feeType']['feeType']['id'] == RefData::FEE_TYPE_CONT,
                    'type' => 'fees'
                ]
            );
            $view->setTemplate('pages/fees/pay-one');
        }

        if ($this->getDisableCardPayments()) {
            $form->get('form-actions')->remove('pay');
            $form->get('form-actions')->get('cancel')->setLabel('back-to-fees');
            $form->get('form-actions')->get('cancel')->setAttribute('class', 'govuk-button govuk-button--secondary');
            $this->guidanceHelper->append('selfserve-card-payments-disabled');
        }

        return $view;
    }

    public function handleResultAction(): \Laminas\Http\Response
    {
        $queryStringData = (array)$this->getRequest()->getQuery();

        $dtoData = [
            'reference' => $queryStringData['receipt_reference'],
            'cpmsData' => $queryStringData,
            'paymentMethod' => self::PAYMENT_METHOD,
        ];

        $response = $this->handleCommand(CompletePayment::create($dtoData));

        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToIndex();
        }

        // check payment status and redirect accordingly
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentById::create(['id' => $paymentId]));
        $payment = $response->getResult();
        switch ($payment['status']['id']) {
            case RefData::TRANSACTION_STATUS_COMPLETE:
                return $this->redirectToReceipt($queryStringData['receipt_reference']);
            case RefData::TRANSACTION_STATUS_CANCELLED:
                break;
            case RefData::TRANSACTION_STATUS_FAILED:
            default:
                $this->addErrorMessage('payment-failed');
                break;
        }
        return $this->redirectToIndex();
    }

    public function receiptAction(): ViewModel
    {
        $paymentRef = $this->params()->fromRoute('reference');

        $viewData = $this->getReceiptData($paymentRef);

        $view = new ViewModel($viewData);
        $view->setTemplate('pages/fees/payment-success');
        return $view;
    }

    protected function getOutstandingFeeDataForOrganisation(?int $organisationId)
    {
        $query = OutstandingFees::create(['id' => $organisationId, 'hideExpired' => true]);
        $response = $this->handleQuery($query);

        $this->disableCardPayments = $response->getResult()['disableCardPayments'];

        return $response->getResult();
    }

    protected function getOutstandingFeesForOrganisation(?int $organisationId)
    {
        $result = $this->getOutstandingFeeDataForOrganisation($organisationId);
        return $result['outstandingFees'];
    }

    /**
     * Are Card payments disabled
     *
     * @return bool
     */
    protected function getDisableCardPayments()
    {
        return $this->disableCardPayments;
    }


    /**
     * Get fees by ID(s) from params, note these *must* be a subset of the
     * outstanding fees for the current organisation - any invalid IDs are
     * ignored
     *
     * @psalm-return list<mixed>
     */
    protected function getFeesFromParams(): array
    {
        $fees = [];

        $organisationId = $this->getCurrentOrganisationId();
        $outstandingFees = $this->getOutstandingFeesForOrganisation($organisationId);

        if (!empty($outstandingFees)) {
            $ids = explode(',', (string) $this->params('fee'));
            foreach ($outstandingFees as $fee) {
                if (in_array($fee['id'], $ids)) {
                    $fees[] = $fee;
                }
            }
        }

        return $fees;
    }

    protected function getForm(): \Common\Form\Form
    {
        return $this->formHelper
            ->createForm('FeePayment');
    }

    /**
     * @return \Laminas\Http\Response|null
     */
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

    protected function redirectToIndex(): \Laminas\Http\Response
    {
        return $this->redirect()->toRoute('fees');
    }

    protected function redirectToReceipt($reference): \Laminas\Http\Response
    {
        return $this->redirect()->toRoute('fees/receipt', ['reference' => $reference]);
    }

    /**
     * Calls command to initiate payment and then redirects
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    protected function payOutstandingFees(array $feeIds)
    {
        $cpmsRedirectUrl = $this->urlHelper
            ->fromRoute('fees/result', [], ['force_canonical' => true], true);

        $paymentMethod = self::PAYMENT_METHOD;
        $organisationId = $this->getCurrentOrganisationId();

        $dtoData = compact('cpmsRedirectUrl', 'feeIds', 'paymentMethod', 'organisationId');
        $dto = PayOutstandingFees::create($dtoData);

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleCommand($dto);

        $messages = $response->getResult()['messages'];
        $translateHelper = $this->translationHelper;
        $errorMessage = '';
        foreach ($messages as $message) {
            if (is_array($message) && array_key_exists(RefData::ERR_WAIT, $message)) {
                $errorMessage = $translateHelper->translate('payment.error.15sec');
                break;
            } elseif (is_array($message) && array_key_exists(RefData::ERR_NO_FEES, $message)) {
                $errorMessage = $translateHelper->translate('payment.error.feepaid');
                break;
            }
        }
        if ($errorMessage !== '') {
            $this->addErrorMessage($errorMessage);
            return $this->redirectToIndex();
        }

        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToIndex();
        }

        // due to CQRS, we now need another request to look up the payment in
        // order to get the redirect data :-/
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentById::create(['id' => $paymentId]));
        $payment = $response->getResult();
        $view = new ViewModel(
            [
                'gateway' => $payment['gatewayUrl'],
                'data' => [
                    'receipt_reference' => $payment['reference']
                ]
            ]
        );
        $view->setTemplate('cpms/payment');

        return $this->render($view);
    }

    /**
     * Late fee action
     *
     * @return ViewModel
     */
    public function lateFeeAction()
    {
        $feeId = $this->params('fee');
        $response = $this->handleQuery(Fee::create(['id' => $feeId]));
        if (!$response->isOk()) {
            throw new ResourceNotFoundException('Fee not found');
        }
        $result = $response->getResult();
        $view = new ViewModel(
            ['licenceExpiryDate' => date('d F Y', strtotime((string) $result['licenceExpiryDate']))]
        );
        $view->setTemplate('pages/fees/late');
        return $this->render($view);
    }
}
