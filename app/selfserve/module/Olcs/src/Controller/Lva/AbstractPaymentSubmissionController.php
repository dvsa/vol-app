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
use Common\Service\Entity\FeePaymentEntityService;
use Common\Service\Entity\PaymentEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Cpms\Exception as CpmsException;
use Common\Service\Cpms\Exception\PaymentInvalidResponseException;
use Common\Service\Processing\ApplicationSnapshotProcessingService;
use Dvsa\Olcs\Transfer\Query\Application\OutstandingFees as AppOutstandingFeesQry;
use Dvsa\Olcs\Transfer\Command\Payment\PayOutstandingFees as PayOutstandingFeesCmd;
use Dvsa\Olcs\Transfer\Query\Payment\Payment as PaymentByIdQry;

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
            $this->addErrorMessage($this->getGenericErrorMessage());
            return $this->redirectToOverview();
        }

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

        // render the gateway redirect
        $view->setTemplate('cpms/payment');
        return $this->render($view);
    }

    /**
     * Handle response from third-party payment gateway
     * @TODO migrate to Command
     */
    public function paymentResultAction()
    {
        $applicationId = $this->getApplicationId();

        // Customer-friendly error message
        $genericErrorMessage = $this->getGenericErrorMessage();

        $query = (array)$this->getRequest()->getQuery();

        try {
            $resultStatus = $this->getServiceLocator()
                ->get('Cpms\FeePayment')
                ->handleResponse($query, FeePaymentEntityService::METHOD_CARD_ONLINE);

        } catch (CpmsException $ex) {
            $this->addErrorMessage($genericErrorMessage);
            return $this->redirectToOverview();
        }

        switch ($resultStatus) {
            case PaymentEntityService::STATUS_PAID:
                $this->updateApplicationAsSubmitted($applicationId);
                $ref = isset($query['receipt_reference']) ? $query['receipt_reference'] : null;
                return $this->redirectToSummary($ref);
            case PaymentEntityService::STATUS_FAILED:
            case PaymentEntityService::STATUS_CANCELLED:
            default:
                $this->addErrorMessage($genericErrorMessage);
                return $this->redirectToOverview();
        }
    }

    protected function redirectToSummary($ref = null)
    {
        return $this->redirect()->toRoute(
            'lva-'.$this->lva.'/summary',
            [
                $this->getIdentifierIndex() => $this->getApplicationId(),
                'reference' => $ref
            ]
        );
    }

    protected function redirectToOverview()
    {
        return $this->redirect()->toRoute(
            'lva-'.$this->lva,
            [$this->getIdentifierIndex() => $this->getApplicationId()]
        );
    }

    /**
     * @TODO move this to backend
     */
    protected function updateApplicationAsSubmitted($applicationId)
    {
        $this->getServiceLocator()->get('Processing\ApplicationSnapshot')
            ->storeSnapshot($applicationId, ApplicationSnapshotProcessingService::ON_SUBMIT);

        $this->getServiceLocator()->get('Processing\Application')
            ->submitApplication($applicationId);

        $this->updateLicenceStatus($applicationId);
    }

    /**
     * Get fees pertaining to the application
     * @TODO move this to app overview
     */
    // protected function getFees($applicationId)
    // {
    //     $query = AppOutstandingFeesQry::create(['id' => $applicationId]);
    //     $response = $this->handleQuery($query);

    //     $fees = $response->getResult()['outstandingFees'];
    //     var_dump($fees);
    //     exit;

    //     return $fees;
    // }

    protected function getGenericErrorMessage()
    {
        return $this->getServiceLocator()->get('translator')->translate('feeNotPaidError');
    }
}
