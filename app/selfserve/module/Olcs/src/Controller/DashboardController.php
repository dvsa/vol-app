<?php

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Olcs\View\Model\Dashboard;
use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Organisation\Dashboard as DashboardQry;
use Zend\View\Model\ViewModel;

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DashboardController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait,
        Lva\Traits\DashboardNavigationTrait;

    /**
     * Dashboard index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        if ($this->isGranted(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD) &&
            !$this->isGranted(RefData::PERMISSION_SELFSERVE_LVA)) {
            $view = $this->transportManagerDashboardView();
        } else {
            $view = $this->standardDashboardView();
        }

        return $view;
    }

    /**
     * Get the Standard Dashboard view
     *
     * @return ViewModel
     */
    protected function standardDashboardView()
    {
        $organisationId = $this->getCurrentOrganisationId();

        if (empty($organisationId)) {
            $this->flashMessenger()->addErrorMessage('auth.login.failed.reason.account-disabled');
            return $this->redirect()->toRoute('auth/login');
        }

        // retrieve data
        $query = DashboardQry::create(['id' => $organisationId]);
        $response = $this->handleQuery($query);
        $dashboardData = $response->getResult()['dashboard'];

        $total = 0;

        if (isset($dashboardData['licences'])
            && isset($dashboardData['applications'])
            && isset($dashboardData['variations'])) {

            $total = count($dashboardData['licences'])
                + count($dashboardData['applications'])
                + count($dashboardData['variations']);
        }

        // build tables
        $params = $this->getServiceLocator()->get('DashboardProcessingService')->getTables($dashboardData);

        $params['total'] = $total;
        $params['showVariationTable'] = count($dashboardData['variations']) > 0;
        $params['showApplicationTable'] = count($dashboardData['applications']) > 0;

        // setup view
        $view = new \Zend\View\Model\ViewModel($params);
        $view->setTemplate('dashboard');

        // populate the navigation tabs with correct counts
        $this->populateTabCounts(
            $dashboardData['feeCount'],
            $dashboardData['correspondenceCount']
        );

        return $view;
    }

    /**
     * Get the Dashboard view for a Transport Manager
     *
     * @return ViewModel
     */
    protected function transportManagerDashboardView()
    {
        $userId = $this->currentUser()->getUserData()['id'];

        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(
                [
                    'user' => $userId,
                    'appStatuses' => [
                        RefData::APPLICATION_STATUS_UNDER_CONSIDERATION,
                        RefData::APPLICATION_STATUS_NOT_SUBMITTED
                    ],
                    'filterByOrgUser' => true
                ]
            )
        );
        $results = $response->getResult()['results'];

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
