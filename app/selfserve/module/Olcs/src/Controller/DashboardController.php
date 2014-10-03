<?php

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Olcs\View\DashboardViewModel;
use Common\Controller\AbstractActionController;

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DashboardController extends AbstractActionController
{
    /**
     * Dashboard index action
     */
    public function indexAction()
    {
        $user = $this->getEntityService('User')->getCurrentUser();
        $applicationService = $this->getEntityService('Application');

        $applications = $applicationService->getForUser($user['id']);

        $view = new DashboardViewModel();
        $view->setServiceLocator($this->getServiceLocator());
        $view->setApplications($applications);

        return $view;
    }
}
