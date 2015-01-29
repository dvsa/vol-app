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
            'lva-application/result',
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
                    'redirectionData' => $response['redirection_data']
                ]
            ]
        );

        $view->setTemplate('cpms/payment');
        return $this->render($view);
    }

    /**
     * Handle response from third-party payment gateway
     *
     * @todo we should probably look up the fee id be by receipt reference
     * rather than have it passed as a parameter on the redirect Url
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
                    array($this->getFeeFromParams())
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
        $update = array(
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
            'receivedDate' =>
                $this->getServiceLocator()
                    ->get('Helper\Date')->getDateObject()->format('Y-m-d H:i:s'),
            'targetCompletionDate' =>
                $this->getServiceLocator()
                    ->get('Helper\Date')->getDateObject()->modify('+9 week')->format('Y-m-d H:i:s')
        );

        $this->getServiceLocator()
            ->get('Entity\Application')
            ->forceUpdate($applicationId, $update);

        $actionDate = $this->getServiceLocator()->get('Helper\Date')->getDate();

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

    /**
     * Helper to retrieve fee object from parameter
     */
    private function getFeeFromParams()
    {
        $id = $this->params('fee');
        return $this->getServiceLocator()->get('Entity\Fee')->getOverview($id);
    }

    protected function getOrganisationForApplication($applicationId)
    {
        return $this->getServiceLocator()->get('Entity\Application')->getOrganisation($applicationId);
    }
}
