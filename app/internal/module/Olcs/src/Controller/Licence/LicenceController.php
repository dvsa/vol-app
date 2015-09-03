<?php

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Cases\ByLicence as CasesByLicenceQry;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Traits;
use Olcs\Controller\Lva;
use Zend\View\Model\ViewModel;

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class LicenceController extends AbstractController implements LicenceControllerInterface
{
    use Lva\Traits\LicenceControllerTrait,
        Traits\TaskSearchTrait,
        Traits\DocumentSearchTrait,
        Traits\DocumentActionTrait,
        Traits\FeesActionTrait;

    protected $pageLayout = 'licence-section';

    /**
     * Route (prefix) for fees action redirects
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'licence/fees';
    }

    /**
     * The fees route redirect params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [
            'licence' => $this->params()->fromRoute('licence')
        ];
    }

    /**
     * The controller specific fees table params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesTableParams()
    {
        return [
            'licence' => $this->params()->fromRoute('licence'),
            'status' => 'current',
        ];
    }

    public function detailsAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view);
    }

    public function casesAction()
    {
        $this->checkForCrudAction('case', [], 'case');
        $view = $this->getViewWithLicence();

        $params = [
            'licence' => $this->getQueryOrRouteParam('licence'),
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', 'createdOn, id'),
            'order'   => $this->getQueryOrRouteParam('order', 'DESC'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
        ];

        $params['query'] = $this->getRequest()->getQuery()->toArray();

        $response = $this->handleQuery(CasesByLicenceQry::create($params));
        $results = $response->getResult();

        // If this is an 'unlicensed' licence, redirect to the Unlicensed
        // Operator version of the page
        if ($results['extra']['licence']['status']['id'] === RefData::LICENCE_STATUS_UNLICENSED) {
            return $this->redirect()->toRoute(
                'operator-unlicensed/cases',
                ['organisation' => $results['extra']['organisation']['id']]
            );
        }

        $view->{'table'} = $this->getTable('cases', $results, $params);

        $view->setTemplate('partials/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        $this->loadScripts(['table-actions']);

        return $this->renderView($view);
    }

    /**
     * Opposition page
     */
    public function oppositionAction()
    {
        $licenceId = (int) $this->params()->fromRoute('licence', null);

        $responseOppositions = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Opposition\OppositionList::create(
                [
                    'licence' => $licenceId,
                    'sort' => 'raisedDate',
                    'order' => 'ASC',
                    'page' => 1,
                    'limit' => 1000,
                ]
            )
        );
        if (!$responseOppositions->isOk()) {
            throw new \RuntimeException('Cannot get Opposition list');
        }
        $oppositionResults = $responseOppositions->getResult()['results'];

        /* @var $oppositionHelperService \Common\Service\Helper\OppositionHelperService */
        $oppositionHelperService = $this->getServiceLocator()->get('Helper\Opposition');
        $oppositions = $oppositionHelperService->sortOpenClosed($oppositionResults);

        $responseComplaints = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\EnvironmentalComplaint\EnvironmentalComplaintList::create(
                [
                    'licence' => $licenceId,
                    'sort' => 'complaintDate',
                    'order' => 'ASC',
                    'page' => 1,
                    'limit' => 1000,
                ]
            )
        );
        if (!$responseComplaints->isOk()) {
            throw new \RuntimeException('Cannot get Complaints list');
        }
        $casesResults = $responseComplaints->getResult()['results'];

        /* @var $complaintsHelperService \Common\Service\Helper\ComplaintsHelperService */
        $complaintsHelperService = $this->getServiceLocator()->get('Helper\Complaints');
        $complaints = $complaintsHelperService->sortCasesOpenClosed($casesResults);

        $view = new ViewModel(
            [
                'tables' => [
                    $this->getTable('opposition-readonly', $oppositions),
                    $this->getTable('environmental-complaints-readonly', $complaints)
                ]
            ]
        );
        $view->setTemplate('pages/multi-tables');

        return $this->renderView($view);
    }


    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'licence/documents';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['licence' => $this->getFromRoute('licence')];
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->mapDocumentFilters(['licence' => $this->getFromRoute('licence')]);

        return $this->getViewWithLicence(
            [
                'table' => $this->getDocumentsTable($filters),
                'form'  => $this->getDocumentForm($filters)
            ]
        );
    }

    public function busAction()
    {
        $this->checkForCrudAction('licence/bus/registration');

        $searchData = array(
            'licId' => $this->getFromRoute('licence'),
            'page' => 1,
            'sort' => 'regNo',
            'order' => 'DESC',
            'limit' => 10
        );

        $filters = array_merge(
            $searchData,
            $this->getRequest()->getQuery()->toArray()
        );

        // if status is set to all
        if (isset($filters['status']) && !$filters['status']) {
            unset($filters['status']);
        }

        $resultData = $this->makeRestCall('BusRegSearchView', 'GET', $filters, []);

        $table = $this->getTable(
            'busreg',
            $resultData,
            array_merge(
                $filters,
                array('query' => $this->getRequest()->getQuery())
            ),
            true
        );

        $form = $this->getForm('bus-reg-list');
        $form->remove('csrf'); //we never post
        $form->setData($filters);

        $this->setTableFilters($form);

        $this->loadScripts(['forms/filter', 'table-actions']);

        $view = $this->getViewWithLicence(
            array(
                'table' => $table
            )
        );

        $view->setTemplate('layout/bus-registrations-list');

        $view->setTerminal(
            $this->getRequest()->isXmlHttpRequest()
        );

        return $this->renderView($view);
    }

    /**
     * This method is to assist the hierarchical nature of zend
     * navigation when parent pages need to also be siblings
     * from a breadcrumb and navigation point of view.
     *
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('licence/details/overview', [], [], true);
    }

    protected function renderLayout($view)
    {
        $tmp = $this->getViewWithLicence($view->getVariables());
        $view->setVariables($tmp->getVariables());

        return $this->renderView($view);
    }
}
