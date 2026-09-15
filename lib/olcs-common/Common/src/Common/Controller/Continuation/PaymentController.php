<?php

namespace Common\Controller\Continuation;

use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePayment;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentById;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * PaymentController
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PaymentController extends AbstractContinuationController
{
    protected $layout = 'pages/fees/pay-one';

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        TranslationHelperService $translationHelper,
        protected UrlHelperService $urlHelper,
        protected TableFactory $tableFactory
    ) {
        parent::__construct($niTextTranslationUtil, $authService, $formServiceManager, $translationHelper);
    }

    /**
     * Index page
     */
    #[\Override]
    public function indexAction()
    {
        $data = $this->getContinuationDetailData();
        $fees = $data['fees'];

        $request = $this->getRequest();

        if ($request->isPost()) {
            return $this->payFees(
                array_column($fees, 'id'),
                $data['licence']['organisation']['id']
            );
        }

        if (empty($fees)) {
            $this->addSuccessMessage('payment.error.feepaid');
            return $this->redirectToSuccessPage();
        }

        /* @var $form \Common\Form\Form */
        $form = $this->getForm('continuations-payment', $data);
        $fee = reset($fees);

        $viewVariables = [
            'form' => $form,
            'payingFromFlow' => true,
            'hasContinuation' => true,
            'type' => 'fees'
        ];
        if (count($fees) > 1) {
            $table = $this->tableFactory->buildTable('pay-fees', $fees, [], false);
            $viewVariables['table'] = $table;
            $this->layout = 'pages/fees/pay-multi';
        } else {
            $viewVariables['fee'] = $fee;
        }

        return $this->getViewModel($data['licence']['licNo'], $form, $viewVariables);
    }

    /**
     * Calls command to initiate payment and then redirects
     *
     * @param array        $feeIds              fee id
     * @param int          $organisationId      organisation id
     */
    protected function payFees($feeIds, $organisationId)
    {
        $cpmsRedirectUrl = $this->url()->fromRoute(
            'continuation/payment/result',
            [],
            ['force_canonical' => true],
            true
        );

        $paymentMethod = RefData::FEE_PAYMENT_METHOD_CARD_ONLINE;
        $dtoData = ['cpmsRedirectUrl' => $cpmsRedirectUrl, 'feeIds' => $feeIds, 'paymentMethod' => $paymentMethod, 'organisationId' => $organisationId ];

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleCommand(PayOutstandingFees::create($dtoData));

        $result = $this->handleResponse($response);
        if ($result !== null) {
            return $result;
        }

        $paymentId = $response->getResult()['id']['transaction'];

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleQuery(PaymentById::create(['id' => $paymentId]));
        $payment = $response->getResult();
        $viewVariables = [
            'gateway' => $payment['gatewayUrl'],
            'data' => ['receipt_reference' => $payment['reference']]
        ];
        $this->layout = 'cpms/payment';

        return $this->getViewModel('', null, $viewVariables);
    }

    /**
     * Handle response
     *
     * @param \Common\Service\Cqrs\Response $response response
     *
     * @return null|\Laminas\Http\Response
     */
    protected function handleResponse($response)
    {
        $errorMessage = '';

        $messages = $response->getResult()['messages'];

        foreach ($messages as $message) {
            if (is_array($message) && array_key_exists(RefData::ERR_WAIT, $message)) {
                $errorMessage = $this->translationHelper->translate('payment.error.15sec');
                break;
            } elseif (is_array($message) && array_key_exists(RefData::ERR_NO_FEES, $message)) {
                $errorMessage = $this->translationHelper->translate('payment.error.feepaid');
                break;
            }
        }

        if ($errorMessage !== '') {
            $this->addErrorMessage($errorMessage);
            return $this->redirectToPaymentPage();
        }

        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToPaymentPage();
        }

        return null;
    }

    /**
     * Handle payment result
     *
     * @return null|\Laminas\Http\Response
     */
    public function resultAction()
    {
        $queryStringData = (array) $this->getRequest()->getQuery();
        if ($queryStringData === []) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToPaymentPage();
        }

        $dtoData = [
            'reference' => $queryStringData['receipt_reference'],
            'cpmsData' => $queryStringData,
            'paymentMethod' => RefData::FEE_PAYMENT_METHOD_CARD_ONLINE,
        ];

        $response = $this->handleCommand(CompletePayment::create($dtoData));

        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToPaymentPage();
        }

        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentById::create(['id' => $paymentId]));
        $payment = $response->getResult();

        switch ($payment['status']['id']) {
            case RefData::TRANSACTION_STATUS_COMPLETE:
                $this->addSuccessMessage('payment-completed');
                return $this->redirectToSuccessPage();
            case RefData::TRANSACTION_STATUS_CANCELLED:
                $this->addErrorMessage('payment-cancelled');
                break;
            case RefData::TRANSACTION_STATUS_FAILED:
            default:
                $this->addErrorMessage('payment-failed');
        }

        return $this->redirectToPaymentPage();
    }
}
