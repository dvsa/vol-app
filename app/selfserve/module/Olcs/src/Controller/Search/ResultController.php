<?php
/**
 * Search Result Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace Olcs\Controller\Search;

use Common\Controller\Lva\AbstractController;
use Common\Service\Entity\UserEntityService;
use Dvsa\Olcs\Transfer\Query\Search\Licence as SearchLicence;

/**
 * Search Result Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ResultController extends AbstractController
{

    private $licenceSections = [
        'licence' => 'overview',
        /*'organisation' => 'overview',
        'contactDetails' => 'overview',
        'directors' => 'table',
        'transportManagers' => 'table',
        'operatingCentres' => 'table',
        'vehicles' => 'table',
        'applications' => 'table',
        'conditionUndertakings' => 'table',
        'otherLicences' => 'table'*/
    ];

    public function detailsAction()
    {
        $action = $this->params()->fromRoute('entity') . 'Action';

        if (method_exists($this, $action)) {

            return $this->$action();
        }

        return $this->notFoundAction();
    }

    /**
     * Operator index action
     */
    public function licenceAction()
    {
        $entityId = $this->params()->fromRoute('entityId');

        // retrieve data
        $query = SearchLicence::create(['id' => $entityId]);
        $response = $this->handleQuery($query);

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $result = $response->getResult();

            //$searchResultSectionData = $this->processSearchResultData($this->licenceSections, $result);
        }

        // setup view

        $content = new \Zend\View\Model\ViewModel(
            [
                'result' => $result
            ]
        );

        $content->setTemplate('olcs/search/search-result');

        $layout = new \Zend\View\Model\ViewModel(
            [
                'pageTitle' => 'Big Wagons Limited',

            ]
        );
        $layout->setTemplate('layouts/search-result');
        $layout->addChild($content, 'content');

        return $layout;
    }

    private function processSearchResultData(array $sections, array $searchResultData)
    {

        $searchResultSectionData = [];
        foreach ($sections as $section => $viewType) {

            if (isset($searchResultData[$section])) {
                if ($viewType === 'table') {
                    $searchResultSectionData[$section] = $this->table()
                        ->buildTable('search-result/' . $section, $searchResultData[$section], [])
                        ->render();
                } elseif ($viewType === 'overview') {
                    $searchResultSectionData[$section] = $searchResultData[$section];
                }
            }
        }

        return $searchResultSectionData;
    }

    /**
     * Get the Standard Dashboard view
     *
     * @return Dashboard
     */
    protected function standardDashboardView()
    {
        $organisationId = $this->getCurrentOrganisationId();

        // retrieve data
        $query = OrganisationQry::create(['id' => $organisationId]);
        $response = $this->handleQuery($query);
        $searchResultData = $response->getResult();

        // build tables
        $tables = $this->getServiceLocator()->get('DashboardProcessingService')->getTables($dashboardData);

        // setup view
        $view = new \Zend\View\Model\ViewModel($tables);
        $view->setTemplate('dashboard');

        // populate the navigation tabs with correct counts
        $this->populateTabCounts();

        return $view;
    }

    /**
     * Get the Dashboard view for a Transport Manager
     */
    protected function transportManagerDashboardView()
    {
        $userId = $this->currentUser()->getUserData()['id'];

        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(['user' => $userId])
        );
        $results = $response->getResult()['result'];

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
