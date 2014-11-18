<?php

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Olcs\View\Model\Dashboard;
use Common\Controller\Lva\AbstractController;

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DashboardController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait;

    /**
     * Dashboard index action
     */
    public function indexAction()
    {
        $organisationId = $this->getCurrentOrganisationId();
        $applicationService = $this->getServiceLocator()->get('Entity\Application');

        $applications = $applicationService->getForOrganisation($organisationId);

        $view = new Dashboard();
        $view->setServiceLocator($this->getServiceLocator());
        $view->setApplications($applications);

        return $view;
    }
}
