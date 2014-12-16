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

/**
 * External Application Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
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

        $update = array(
            'id' => $applicationId,
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
            'version' => $data['version'],
            'receivedDate' =>
                $this->getServiceLocator()->get('Helper\Date')->getDateObject()->format('Y-m-d H:i:s'),
            'targetCompletionDate' =>
                $this->getServiceLocator()->get('Helper\Date')->getDateObject()->modify('+9 week')->format('Y-m-d H:i:s')
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
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_GRANT_FEE_DUE,
            'description' => 'GV79 Application',
            'actionDate' => $actionDate,
            'assignedByUser' => 1,
            'assignedToUser' => 1,
            'isClosed' => 0,
            'application' => $this->getApplicationId(),
            'licence' => $this->getLicenceId()
        );

        $this->getServiceLocator()
            ->get('Entity\Task')
            ->save($task);

        return $this->redirect()->toRoute('lva-application/summary', [$this->getIdentifierIndex() => $applicationId]);
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
}
