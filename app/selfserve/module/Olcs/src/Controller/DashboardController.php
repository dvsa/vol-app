<?php

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Olcs\View\Model\Dashboard;
use Common\Controller\Lva\AbstractController;
use \Common\Service\Entity\UserEntityService;

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
        if ($this->isGranted(UserEntityService::PERMISSION_SELFSERVE_TM_DASHBOARD) &&
            !$this->isGranted(UserEntityService::PERMISSION_SELFSERVE_LVA)) {
            $view = $this->transportManagerDashboardView();
        } else {
            $view = $this->standardDashboardView();
        }

        return $view;
    }

    /**
     * Get the Standard Dashboard view
     *
     * @return Dashboard
     */
    protected function standardDashboardView()
    {
        $organisationId = $this->getCurrentOrganisationId();
        $applicationService = $this->getServiceLocator()->get('Entity\Application');

        $applications = $applicationService->getForOrganisation($organisationId);

        $results = $this->getServiceLocator()->get('DashboardProcessingService')->getTables($applications);

        // setup view
        $view = new \Zend\View\Model\ViewModel($results);
        $view->setTemplate('dashboard');

        return $view;
    }

    /**
     * Get the Dashboard view for a Transport Manager
     */
    protected function transportManagerDashboardView()
    {
        // get data
        $results = $this->getServiceLocator()->get('Entity\User')->getTransportManagerApplications(
            $this->getLoggedInUser()
        );

        // flatten the array
        $data = $this->getServiceLocator()->get('DataMapper\DashboardTmApplications')->map($results);

        // create table
        $table = $this->getServiceLocator()->get('Table')->buildTable('dashboard-tm-applications', $data);

        // setup view
        $view = new \Zend\View\Model\ViewModel();
        $view->setTemplate('dashboard-tm');
        $view->setVariable('applicationsTable', $table);

        return $view;
    }
}
