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
        $data = (array)$this->getRequest()->getPost();

        // bail out if we don't have an application id or this isn't a form POST
        if (!$this->getRequest()->isPost() || empty($applicationId)) {
            throw new BadRequestException('Invalid payment submission request');
        }

        $fee = $this->getServiceLocator()->get('Entity\Fee')
            ->getLatestOutstandingFeeForApplication($applicationId);

        $salesReference    = $fee['id'];
        $organisation      = $this->getOrganisationForApplication($applicationId);
        $customerReference = $organisation['id'];
        $paymentType       = FeePaymentEntityService::METHOD_CARD_ONLINE;

        $redirectUrl = $this->url()->fromRoute(
            'lva-application/result',
            ['action' => 'payment-result'],
            ['force_canonical' => true],
            true
        );

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
            //return $this->redirectToOverview(); // @TODO
            throw new \Exception($msg);
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
        
//http://olcs-selfserve/application/1/summary/?state=0.36094300+1421079426&receipt_reference=OLCS-01-20150112-161706-9C06C3D4&code=802&message=The+third+party+gateway+has+responded+with+a+failure


    }

    public function summaryAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();

            if (isset($data['submitDashboard'])) {
                return $this->redirect()->toRoute('dashboard');
            }

            // otherwise just assume we want to view our application summary
            return $this->redirect()->toRoute(
                'lva-application',
                [$this->getIdentifierIndex() => $this->getApplicationId()]
            );
        }
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Lva\PaymentSummary');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('summary-application');

        return $this->render($view);
    }

//
    /**
     * Handle response from third-party payment gateway
     */
    public function paymentResultAction()
    {
        $applicationId = $this->getApplicationId();

        // @todo we need a customer-friendly translatable string here
        $genericErrorMessage = 'The fee was not paid, please try again';
        try {
            $resultStatus = $this->getServiceLocator()
                ->get('Cpms\FeePayment')
                ->handleResponse(
                    (array)$this->getRequest()->getQuery(),
                    $this->getFeesFromParams()
                );

        } catch (PaymentException $ex) {
            $this->addErrorMessage($genericErrorMessage);
            return $this->redirectToOverview();
        }

        switch ($resultStatus) {
            case PaymentEntityService::STATUS_PAID:
                $this->updateApplicationAsPaid($applicationId);
                $this->addSuccessMessage('The fee(s) have been paid successfully');
                return $this->redirectToSummary();
                break;
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
            'id' => $applicationId,
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
            //'version' => $data['version'], // @TODO do we need this?
            'receivedDate' =>
                $this->getServiceLocator()
                    ->get('Helper\Date')->getDateObject()->format('Y-m-d H:i:s'),
            'targetCompletionDate' =>
                $this->getServiceLocator()
                    ->get('Helper\Date')->getDateObject()->modify('+9 week')->format('Y-m-d H:i:s')
        );

        $this->getServiceLocator()
            ->get('Entity\Application')
            ->save($update);

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
     * Helper to retrieve fee objects from parameters
     */
    private function getFeesFromParams()
    {
        $ids = explode(',', $this->params('fee'));

        $fees = [];

        foreach ($ids as $id) {
            $fees[] = $this->getServiceLocator()
                ->get('Entity\Fee')
                ->getOverview($id);
        }

        return $fees;
    }

    protected function getOrganisationForApplication($applicationId)
    {
        return $this->getServiceLocator()->get('Entity\Application')->getOrganisation($applicationId);
    }
}
