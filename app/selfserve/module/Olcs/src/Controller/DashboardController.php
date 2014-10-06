<?php

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Olcs\View\Model\Dashboard;
use Olcs\Controller\AbstractExternalController;

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DashboardController extends AbstractExternalController
{
    /**
     * Dashboard index action
     */
    public function indexAction()
    {
        $organisation = $this->getCurrentOrganisation();
        $applicationService = $this->getEntityService('Application');

        $applications = $applicationService->getForOrganisation($organisation['id']);

        $view = new Dashboard();
        $view->setServiceLocator($this->getServiceLocator());
        $view->setApplications($applications);

        return $view;
    }
}
