<?php

/**
 * External Abstract Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Common\Exception\BadRequestException;
use Common\Service\Entity\FeePaymentEntityService;
use Common\Service\Entity\PaymentEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Cpms\Exception as CpmsException;
use Common\Service\Cpms\Exception\PaymentInvalidResponseException;
use Common\Service\Processing\ApplicationSnapshotProcessingService;

/**
 * External Abstract Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractPaymentSubmissionController extends AbstractController
{
    protected $lva;
    protected $location = 'external';

    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        // bail out if we don't have an application id or this isn't a form POST
        if (!$this->getRequest()->isPost() || empty($applicationId)) {
            throw new BadRequestException('Invalid payment submission request');
        }

        $data = (array)$this->getRequest()->getPost();

        $fees = $this->getFees($applicationId);

        if (empty($fees)) {
            // no fee to pay
            $this->updateApplicationAsSubmitted($applicationId);
            return $this->redirectToSummary();
        }

        // Check for and resolve any outstanding payment requests
        $service = $this->getServiceLocator()->get('Cpms\FeePayment');
        $feesToPay = [];
        foreach ($fees as $fee) {
            if ($service->hasOutstandingPayment($fee)) {
                $paid = $service->resolveOutstandingPayments($fee);
                if (!$paid) {
                    $feesToPay[] = $fee;
                }
            } else {
                $feesToPay[] = $fee;
            }
        }
        if (empty($feesToPay)) {
            $this->updateApplicationAsSubmitted($applicationId);
            return $this->redirectToSummary();
        }

        $organisation      = $this->getOrganisationForApplication($applicationId);
        $customerReference = $organisation['id'];

        $redirectUrl = $this->url()->fromRoute(
            'lva-'.$this->lva.'/result',
            ['action' => 'payment-result'],
            ['force_canonical' => true],
            true
        );

        try {
            $response = $service->initiateCardRequest($customerReference, $redirectUrl, $feesToPay);
        } catch (PaymentInvalidResponseException $e) {
            $msg = 'Invalid response from payment service. Please try again';
            $this->addErrorMessage($msg);
            return $this->redirectToOverview();
        }

        $view = new ViewModel(
            [
                'gateway' => $response['gateway_url'],
                'data' => [
                    'receipt_reference' => $response['receipt_reference']
                ]
            ]
        );

        $view->setTemplate('cpms/payment');
        return $this->render($view);
    }

    /**
     * Handle response from third-party payment gateway
     */
    public function paymentResultAction()
    {
        $applicationId = $this->getApplicationId();

        // Customer-friendly error message
        $genericErrorMessage = $this->getServiceLocator()->get('translator')
            ->translate('feeNotPaidError');

        try {
            $resultStatus = $this->getServiceLocator()
                ->get('Cpms\FeePayment')
                ->handleResponse(
                    (array)$this->getRequest()->getQuery(),
                    FeePaymentEntityService::METHOD_CARD_ONLINE
                );

        } catch (CpmsException $ex) {
            $this->addErrorMessage($genericErrorMessage);
            return $this->redirectToOverview();
        }

        switch ($resultStatus) {
            case PaymentEntityService::STATUS_PAID:
                $this->updateApplicationAsSubmitted($applicationId);
                return $this->redirectToSummary();
            case PaymentEntityService::STATUS_FAILED:
            case PaymentEntityService::STATUS_CANCELLED:
            default:
                $this->addErrorMessage($genericErrorMessage);
                return $this->redirectToOverview();
        }
    }

    protected function redirectToSummary()
    {
        return $this->redirect()->toRoute(
            'lva-'.$this->lva.'/summary',
            [$this->getIdentifierIndex() => $this->getApplicationId()]
        );
    }

    protected function redirectToOverview()
    {
        return $this->redirect()->toRoute(
            'lva-'.$this->lva,
            [$this->getIdentifierIndex() => $this->getApplicationId()]
        );
    }

    protected function updateApplicationAsSubmitted($applicationId)
    {
        $this->getServiceLocator()->get('Processing\ApplicationSnapshot')
            ->storeSnapshot($applicationId, ApplicationSnapshotProcessingService::ON_SUBMIT);

        $this->getServiceLocator()->get('Processing\Application')
            ->submitApplication($applicationId);

        $this->updateLicenceStatus($applicationId);
    }

    protected function getOrganisationForApplication($applicationId)
    {
        return $this->getServiceLocator()->get('Entity\Application')->getOrganisation($applicationId);
    }

    /**
     * Get fees pertaining to the application
     *
     * Note we do not simply call FeeEntityService::getOutstandingFeesForApplication()
     * as AC specify we should only get the *latest* application and interim
     * fees in the event there are multiple fees outstanding.
     */
    protected function getFees($applicationId)
    {
        $fees = [];
        $processingService = $this->getServiceLocator()->get('Processing\Application');

        $applicationFee = $processingService->getApplicationFee($applicationId);
        if (!empty($applicationFee)) {
            $fees[] = $applicationFee;
        }

        $category = $this->getServiceLocator()->get('Entity\Application')->getCategory($applicationId);
        if ($category === LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $interimFee = $processingService->getInterimFee($applicationId);
            if (!empty($interimFee)) {
                $fees[] = $interimFee;
            }
        }

        return $fees;
    }
}
