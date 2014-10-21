<?php

/**
 * Search controller
 * Search for operators and licences
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Zend\Session\Container;
use Zend\View\Model\ViewModel;

/**
 * Search controller
 * Search for operators and licences
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SearchController extends AbstractController
{

    public function processSearchData()
    {
        //there are multiple places search data can come from:
        //route, query, post and session

        //there are lots of params we are interested in:
        //filters, index, search query, page, limit

        if ($this->getRequest()->isPost()) {
            //a post request can come from two forms a) the filter form, b) the query form

            $search = trim($this->params()->fromPost('search'));

            if (!empty($search)) {
                $form = $this->getSearchForm();
                $form->setData($this->params()->fromPost());

                if ($form->isValid()) {
                    //save to session, reset filters in session...
                    //get index from post as well, override what is in the route match
                    $data = $form->getData();
                    $this->getEvent()->getRouteMatch()->setParam('index', $data['index']);
                }
            }
        }
    }

    public function indexAction()
    {
        $this->processSearchData();
        $data = $this->getSearchForm()->getData();

        if (empty($data['search'])) {
            return 'what do you want to find?';
        }

        /** @var \Olcs\Service\Data\Search\Search $searchService **/
        $searchService = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Search\Search');

        $searchService->setIndex($data['index']);
        $searchService->setParams($data['search']);

        $view = new ViewModel();

        $view->indexes = $searchService->getNavigation();
        $view->results = $searchService->fetchResultsTable();

        $view->setTemplate('search/results');

        return $this->renderView($view, 'Search results');
    }

    /**
     * Search form action
     *
     * @return ViewModel
     */
    public function advancedAction()
    {
        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(array('search' => array()));
        $form = $this->generateFormWithData('search', 'processSearch');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('search/index');

        return $this->renderView($view, 'Search', 'Search for licences using any of the following fields');
    }

    /**
     * Process the search
     *
     * @param array $data
     */
    public function processSearch($data)
    {
        $data = array_merge($data['search'], $data['search-advanced']);
        $personSearch = array(
            'forename',
            'familyName',
            'birthDate',
            'transportManagerId'
        );

        $searchType = 'operators';

        foreach ($data as $key => $value) {

            if (empty($value)) {
                unset($data[$key]);
            } elseif (in_array($key, $personSearch)) {
                $searchType = 'person';
            }
        }
        $url = $this->url()->fromRoute('operators/operators-params', $data);

        $this->redirect()->toUrl($url);
    }

    /**
     * Operator search results
     *
     * @return ViewModel
     */
    public function operatorAction()
    {
        $data = $this->params()->fromRoute();
        $results = $this->makeRestCall('OperatorSearch', 'GET', $data);

        $config = $this->getServiceLocator()->get('Config');
        $static = $config['static-list-data'];

        foreach ($results['Results'] as $key => $result) {

            $orgType = $result['organisation_type'];

            if (isset($static['business_types'][$orgType])) {
                $results['Results'][$key]['organisation_type'] = $static['business_types'][$orgType];
            }
        }

        $table = $this->getTable('operator', $results, $data);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('results-operator');
        return $this->renderView($view, 'Search results');
    }
}
