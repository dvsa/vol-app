<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Traits\GenericReceipt;
use Common\RefData;
use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\View\Helper\IrhpApplicationSection;
use Zend\Http\Response as HttpResponse;
use Zend\View\Model\ViewModel;

class IrhpApplicationFeeController extends AbstractSelfserveController implements ToggleAwareInterface
{
    use ExternalControllerTrait;
    use GenericReceipt;
    use FlashMessengerTrait;

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_FEE,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_NOT_SUBMITTED,
        'payment-result' => ConditionalDisplayConfig::IRHP_APP_SUBMITTED,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_FEE,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-fee'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ]
    ];

    protected $postConfig = [
        'default' => [
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_PAYMENT_ACTION,
        ],
    ];

    protected $currentMessages = [];

    /**
     * Attach messages to display in the current response
     *
     * @return void
     */
    protected function attachCurrentMessages()
    {
        foreach ($this->currentMessages as $namespace => $messages) {
            foreach ($messages as $message) {
                $this->addMessage($message, $namespace);
            }
        }
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function paymentAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $redirectUrl = $this->url()->fromRoute(
            IrhpApplicationSection::ROUTE_PAYMENT_RESULT_ACTION,
            ['id' => $id],
            ['force_canonical' => true]
        );

        $dtoData = [
            'cpmsRedirectUrl' => $redirectUrl,
            'irhpApplication' => $id,
            'paymentMethod' =>  RefData::FEE_PAYMENT_METHOD_CARD_ONLINE
        ];

        $dto = PayOutstandingFees::create($dtoData);
        $response = $this->handleCommand($dto);

        $messages = $response->getResult()['messages'];

        $translateHelper = $this->getServiceLocator()->get('Helper\Translation');
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
            return $this->redirect()
                ->toRoute(IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $id]);
        }

        if (!$response->isOk()) {
            $this->addErrorMessage('feeNotPaidError');
            return $this->redirect()
                ->toRoute(IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $id]);
        }

        // Look up the new payment in order to get the redirect data
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
        $payment = $response->getResult();

        $view = new ViewModel(
            [
                'gateway' => $payment['gatewayUrl'],
                'data' => [
                    'receipt_reference' => $payment['reference']
                ]
            ]
        );
        // render the gateway redirect
        $view->setTemplate('cpms/payment');
        return $this->render($view);
    }

    /**
     * Handle response from third-party payment gateway
     *
     * @return HttpResponse
     */
    public function paymentResultAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $queryStringData = (array)$this->getRequest()->getQuery();

        $reference = isset($queryStringData['receipt_reference']) ? $queryStringData['receipt_reference'] : null;

        $dtoData = [
            'reference' => $reference,
            'cpmsData' => $queryStringData,
            'paymentMethod' => RefData::FEE_PAYMENT_METHOD_CARD_ONLINE,
        ];

        $response = $this->handleCommand(CompletePaymentCmd::create($dtoData));
        $this->handlePaymentError($response);

        // check payment status and redirect accordingly
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
        $payment = $response->getResult();

        switch ($payment['status']['id']) {
            case RefData::TRANSACTION_STATUS_COMPLETE:
                return $this->redirect()
                    ->toRoute(
                        IrhpApplicationSection::ROUTE_SUBMITTED,
                        ['id' => $id],
                        ['query' => ['receipt_reference' => $this->params()->fromQuery('receipt_reference')]]
                    );
            case RefData::TRANSACTION_STATUS_CANCELLED:
                return $this->redirect()
                    ->toRoute(IrhpApplicationSection::ROUTE_FEE, ['id' => $id]);
            case RefData::TRANSACTION_STATUS_FAILED:
                return $this->redirect()
                    ->toRoute(IrhpApplicationSection::ROUTE_FEE, ['id' => $id]);
            default:
                break;
        }

        return $this->redirect()
            ->toRoute(IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $id]);
    }

    /**
     * @param CqrsResponse $response payment response
     */
    protected function handlePaymentError(CqrsResponse $response)
    {
        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
        }
    }
}
