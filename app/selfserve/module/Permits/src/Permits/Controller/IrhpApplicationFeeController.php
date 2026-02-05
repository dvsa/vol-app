<?php

namespace Permits\Controller;

use Common\Controller\Traits\GenericReceipt;
use Common\RefData;
use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplication;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Laminas\Http\Response as HttpResponse;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\DataSource\IrhpFeeBreakdown as IrhpFeeBreakdownDataSource;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpApplicationFeeController extends AbstractSelfserveController
{
    use ExternalControllerTrait;
    use GenericReceipt;
    use FlashMessengerTrait;

    protected $lva;

    private const array FEE_BREAKDOWN_TABLES = [
        RefData::IRHP_BILATERAL_PERMIT_TYPE_ID => 'irhp-fee-breakdown-bilateral',
        RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID => 'irhp-fee-breakdown-multilateral',
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_FEE,
    ];

    protected $tableConfig = [
        'default' => TableConfig::IRHP_FEE_BREAKDOWN,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_CAN_PAY_APP_FEE,
        'payment' => ConditionalDisplayConfig::IRHP_APP_HAS_OUTSTANDING_FEES,
        'paymentresult' => ConditionalDisplayConfig::IRHP_APP_HAS_OUTSTANDING_FEES,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_FEE,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-fee'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.page.fee.browser.title',
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
        'payment' => [
            'browserTitle' => 'permits.page.fee.browser.title'
        ]
    ];

    protected $postConfig = [
        'default' => [
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_PAYMENT_ACTION,
            'conditional' => [
                'dataKey' => 'application',
                'params' => 'id',
                'step' => [
                    'command' => SubmitApplication::class,
                    'route' => IrhpApplicationSection::ROUTE_SUBMITTED,
                ],
                'field' => 'hasOutstandingFees',
                'value' => false
            ]
        ],
    ];

    protected $currentMessages = [];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

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
     * @return \Laminas\Http\Response|ViewModel
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

        $errorMessage = !$response->isOk() ? 'feeNotPaidError' : '';
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
            return $this->redirectOnError();
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

        $this->placeholder()
            ->setPlaceholder(
                'pageTitle',
                $this->translationHelper->translate($this->templateVarsConfig['payment']['browserTitle'])
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

        $reference = $queryStringData['receipt_reference'] ?? null;

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
            case RefData::TRANSACTION_STATUS_FAILED:
                return $this->redirectOnError();
            default:
                break;
        }

        return $this->redirect()
            ->toRoute(IrhpApplicationSection::ROUTE_PERMITS);
    }

    /**
     * @param CqrsResponse $response payment response
     */
    protected function handlePaymentError(CqrsResponse $response): void
    {
        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
        }
    }

    protected function redirectOnError(): HttpResponse
    {
        $irhpAppData = $this->data[IrhpAppDataSource::DATA_KEY];

        $route = $irhpAppData['isAwaitingFee']
            ? IrhpApplicationSection::ROUTE_AWAITING_FEE : IrhpApplicationSection::ROUTE_FEE;

        return $this->redirect()->toRoute($route, ['id' => $irhpAppData['id']]);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    #[\Override]
    public function retrieveTables()
    {
        $feeBreakdownRows = $this->data[IrhpFeeBreakdownDataSource::DATA_KEY];

        if (empty($feeBreakdownRows)) {
            $this->tableConfig['default'] = [];
        } else {
            $irhpPermitTypeId =  $this->data[IrhpAppDataSource::DATA_KEY]['irhpPermitType']['id'];
            $this->tableConfig['default']['irhp-fee-breakdown']['tableName'] = self::FEE_BREAKDOWN_TABLES[$irhpPermitTypeId];
        }

        parent::retrieveTables();
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    #[\Override]
    public function retrieveData()
    {
        parent::retrieveData();

        $this->data = $this->mapperManager
            ->get(IrhpApplicationFeeSummary::class)
            ->mapForDisplay($this->data);
    }

    protected function checkForRedirect($lvaId)
    {
        return null;
    }
}
