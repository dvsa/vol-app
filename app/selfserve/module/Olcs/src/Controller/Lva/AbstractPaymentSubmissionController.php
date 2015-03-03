<?php

/**
 * External Abstract Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Service\Entity\ApplicationEntityService;
use Zend\View\Model\ViewModel;
use Common\Service\Data\CategoryDataService;
use Common\Exception\BadRequestException;
use Common\Service\Entity\FeePaymentEntityService;
use Common\Service\Entity\PaymentEntityService;
use Common\Service\Cpms\PaymentException;
use Common\Service\Cpms\PaymentInvalidResponseException;
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

        $fee = $this->getServiceLocator()->get('Entity\Fee')
            ->getLatestOutstandingFeeForApplication($applicationId);

        if (!$fee) {
            // no fee to pay
            $this->updateApplicationAsSubmitted($applicationId);
            return $this->redirectToSummary();
        }

        $organisation      = $this->getOrganisationForApplication($applicationId);
        $customerReference = $organisation['id'];
        $paymentType       = FeePaymentEntityService::METHOD_CARD_ONLINE;

        $redirectUrl = $this->url()->fromRoute(
            'lva-'.$this->lva.'/result',
            ['action' => 'payment-result', 'fee' => $fee['id']],
            ['force_canonical' => true],
            true
        );

        try {
            $response = $this->getServiceLocator()
                ->get('Cpms\FeePayment')
                ->initiateCardRequest(
                    $customerReference,
                    $redirectUrl,
                    array($fee)
                );
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

        } catch (PaymentException $ex) {
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

        $dateHelper = $this->getServiceLocator()->get('Helper\Date');

        $update = array(
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
            'receivedDate' => $dateHelper->getDateObject()->format('Y-m-d H:i:s'),
            'targetCompletionDate' => $dateHelper->getDateObject()->modify('+9 week')->format('Y-m-d H:i:s')
        );

        $this->getServiceLocator()
            ->get('Entity\Application')
            ->forceUpdate($applicationId, $update);

        $actionDate = $dateHelper->getDate();

        $assignment = $this->getServiceLocator()
            ->get('Processing\Task')
            ->getAssignment(['category' => CategoryDataService::CATEGORY_APPLICATION]);

        $task = array_merge(
            [
                'category' => CategoryDataService::CATEGORY_APPLICATION,
                'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
                'description' => $this->getTaskDescription($applicationId),
                'actionDate' => $actionDate,
                'assignedByUser' => 1,
                'isClosed' => 0,
                'application' => $applicationId,
                'licence' => $this->getLicenceId()
            ],
            $assignment
        );

        $this->getServiceLocator()
            ->get('Entity\Task')
            ->save($task);
    }

    protected function getOrganisationForApplication($applicationId)
    {
        return $this->getServiceLocator()->get('Entity\Application')->getOrganisation($applicationId);
    }
}
