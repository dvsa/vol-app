<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Traits\GenericReceipt;
use Common\RefData;
use Common\Util\FlashMessengerTrait;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;

use Permits\View\Helper\EcmtSection;

class FeeController extends AbstractSelfserveController implements ToggleAwareInterface
{

    use ExternalControllerTrait;
    use GenericReceipt;
    use FlashMessengerTrait;

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_WITH_FEE_LIST,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_CAN_BE_SUBMITTED,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_FEE,
    ];

    protected $templateConfig = [
        'generic' => 'permits/fee'
    ];

    protected $postConfig = [
        'default' => [
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_PAYMENT_ACTION,
        ],
    ];


    /**
     * Handle response from third-party payment gateway
     *
     * @return \Zend\Http\Response
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
            'submitEcmtPermitApplicationId' => $id

        ];

        $response = $this->handleCommand(CompletePaymentCmd::create($dtoData));
        $this->handlePaymentError($response);

        // check payment status and redirect accordingly
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
        $payment = $response->getResult();

        if ($this->data['application']['status']['id'] === RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED) {
            $successRoute = EcmtSection::ROUTE_APPLICATION_SUBMITTED;
            $failureRoute = EcmtSection::ROUTE_ECMT_FEE;
        } else {
            $successRoute = EcmtSection::ROUTE_ISSUE_SUBMITTED;
            $failureRoute = EcmtSection::ROUTE_ECMT_AWAITING_FEE;
        }

        switch ($payment['status']['id']) {
            case RefData::TRANSACTION_STATUS_COMPLETE:
                return $this->redirect()
                    ->toRoute($successRoute, ['id' => $id], ['query' => ['receipt_reference' => $this->params()->fromQuery('receipt_reference')]]);
            case RefData::TRANSACTION_STATUS_CANCELLED:
                return $this->redirect()
                    ->toRoute($failureRoute, ['id' => $id]);
            case RefData::TRANSACTION_STATUS_FAILED:
                return $this->redirect()
                    ->toRoute($failureRoute, ['id' => $id]);
            default:
                break;
        }

        return $this->redirect()
            ->toRoute(EcmtSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $id]);
    }

    protected function handlePaymentError($response)
    {
        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
        }
    }
}
