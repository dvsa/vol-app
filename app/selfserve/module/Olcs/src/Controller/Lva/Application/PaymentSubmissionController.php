<?php

/**
 * External Application Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Service\Entity\ApplicationEntityService;
use Zend\View\Model\ViewModel;
use Common\Service\Data\CategoryDataService;
use Common\Exception\BadRequestException;
use Common\Service\Entity\FeePaymentEntityService;
use Common\Service\Entity\PaymentEntityService;
use Common\Service\Cpms\PaymentException;
use Common\Service\Cpms\PaymentInvalidResponseException;

/**
 * External Application Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PaymentSubmissionController extends AbstractController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
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

        $salesReference    = $fee['id'];
        $organisation      = $this->getOrganisationForApplication($applicationId);
        $customerReference = $organisation['id'];
        $paymentType       = FeePaymentEntityService::METHOD_CARD_ONLINE;

        $redirectUrl = $this->url()->fromRoute(
            'lva-application/result',
            ['action' => 'payment-result', 'fee' => $fee['id']],
            ['force_canonical' => true],
            true
        );

        // @TODO should fee id be looked up by receipt reference rather than
        // passed as a param on the redirectUrl?

        try {
            $response = $this->getServiceLocator()
                ->get('Cpms\FeePayment')
                ->initiateCardRequest(
                    $customerReference,
                    $salesReference,
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

    public function summaryAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();

            if (isset($data['submitDashboard'])) {
                return $this->redirect()->toRoute('dashboard');
            }

            // otherwise just assume we want to view our application summary
            // (actually the Overview page)
            return $this->redirectToOverview();
        }
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Lva\PaymentSummary');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('summary-application');

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
                    array($this->getFeeFromParams())
                );

        } catch (PaymentException $ex) {
            $this->addErrorMessage($genericErrorMessage);
            return $this->redirectToOverview();
        }

        switch ($resultStatus) {
            case PaymentEntityService::STATUS_PAID:
                $this->updateApplicationAsPaid($applicationId);
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
            'lva-application/summary',
            [$this->getIdentifierIndex() => $this->getApplicationId()]
        );
    }

    protected function redirectToOverview()
    {
        return $this->redirect()->toRoute(
            'lva-application',
            [$this->getIdentifierIndex() => $this->getApplicationId()]
        );
    }

    protected function updateApplicationAsPaid($applicationId)
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

        // Create a task - OLCS-3297
        // This is set to dummy user account data for the moment
        // @todo Assign task based on traffic area and operator name
        $actionDate = $this->getServiceLocator()->get('Helper\Date')->getDate();
        $task = array(
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => 'GV79 Application',
            'actionDate' => $actionDate,
            'assignedByUser' => 1,
            'assignedToUser' => 1,
            'assignedToTeam' => 2,
            'isClosed' => 0,
            'application' => $applicationId,
            'licence' => $this->getLicenceId()
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
