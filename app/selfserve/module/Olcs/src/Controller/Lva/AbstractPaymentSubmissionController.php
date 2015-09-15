<?php

/**
 * External Abstract Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Zend\View\Model\ViewModel;
use Common\Exception\BadRequestException;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees as PayOutstandingFeesCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication as SubmitApplicationCmd;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;

/**
 * External Abstract Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractPaymentSubmissionController extends AbstractController
{
    const PAYMENT_METHOD = RefData::FEE_PAYMENT_METHOD_CARD_ONLINE;

    protected $lva;
    protected $location = 'external';

    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        // bail out if we don't have an application id or this isn't a form POST
        if (!$this->getRequest()->isPost() || empty($applicationId)) {
            throw new BadRequestException('Invalid payment submission request');
        }

        $redirectUrl = $this->url()->fromRoute(
            'lva-'.$this->lva.'/result',
            ['action' => 'payment-result'],
            ['force_canonical' => true],
            true
        );

        $dtoData = [
            'cpmsRedirectUrl' => $redirectUrl,
            'applicationId' => $applicationId,
            'paymentMethod' => self::PAYMENT_METHOD,
        ];
        $dto = PayOutstandingFeesCmd::create($dtoData);
        $response = $this->handleCommand($dto);

        if (!$response->isOk()) {
            $this->addErrorMessage('feeNotPaidError');
            return $this->redirectToOverview();
        }

        if (empty($response->getResult()['id']['transaction'])) {
            // there were no fees to pay so we don't render the CPMS page
            $postData = (array) $this->getRequest()->getPost();
            return $this->submitApplication($applicationId, $postData['version']);
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

    protected function submitApplication($applicationId, $version)
    {
        $dto = SubmitApplicationCmd::create(
            [
                'id' => $applicationId,
                'version' => $version,
            ]
        );

        $response = $this->handleCommand($dto);

        if ($response->isOk()) {
            return $this->redirectToSummary();
        }

        $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
        return $this->redirectToOverview();
    }

    /**
     * Handle response from third-party payment gateway
     */
    public function paymentResultAction()
    {
        $applicationId = $this->getApplicationId();

        $queryStringData = (array)$this->getRequest()->getQuery();
        $reference = isset($queryStringData['receipt_reference']) ? $queryStringData['receipt_reference'] : null;

        $dtoData = [
            'reference' => $reference,
            'cpmsData' => $queryStringData,
            'paymentMethod' => self::PAYMENT_METHOD,
            'submitApplicationId' => $applicationId,
        ];

        $response = $this->handleCommand(CompletePaymentCmd::create($dtoData));

        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToOverview();
        }

        // check payment status and redirect accordingly
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
        $payment = $response->getResult();
        switch ($payment['status']['id']) {
            case RefData::TRANSACTION_STATUS_COMPLETE:
                return $this->redirectToSummary($reference);
            case RefData::TRANSACTION_STATUS_CANCELLED:
                break;
            case RefData::TRANSACTION_STATUS_FAILED:
            default:
                $this->addErrorMessage('feeNotPaidError');
                break;
        }

        return $this->redirectToOverview();
    }

    protected function redirectToSummary($ref = null)
    {
        return $this->redirect()->toRoute(
            'lva-'.$this->lva.'/summary',
            [
                'application' => $this->getApplicationId(),
                'reference' => $ref
            ]
        );
    }

    protected function redirectToOverview()
    {
        return $this->redirect()->toRoute(
            'lva-'.$this->lva,
            ['application' => $this->getApplicationId()]
        );
    }
}
