<?php

/**
 * Search controller
 * Search for operators and licences
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

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

        //a post request can come from two forms a) the filter form, b) the query form
        $form = $this->getSearchForm();
        $form->setData($this->params()->fromPost());

        if ($form->isValid()) {
            //save to session, reset filters in session...
            //get index from post as well, override what is in the route match
            $data = $form->getData();
            $this->getEvent()->getRouteMatch()->setParam('index', $data['index']);
        }
    }

    public function indexAction()
    {
        $data = $this->getSearchForm()->getObject();
        //override with get route index unless request is post
        if ($this->getRequest()->isPost()) {
            $this->processSearchData();
        }

        //update data with information from route, and rebind to form so that form data is correct
        $data['index'] = $this->params()->fromRoute('index');
        $this->getSearchForm()->setData($data);

        if (empty($data['search'])) {
            $this->flashMessenger()->addErrorMessage('Please provide a search term');
            return $this->redirectToRoute('dashboard');
        }

        /** @var \Olcs\Service\Data\Search\Search $searchService **/
        $searchService = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Search\Search');

        $searchService->setQuery($this->getRequest()->getQuery())
                      ->setIndex($data['index'])
                      ->setSearch($data['search']);

        $view = new ViewModel();

        $view->indexes = $searchService->getNavigation();
        $view->results = $searchService->fetchResultsTable();

        $view->setTemplate('layout/search-results');

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
        $view->setTemplate('partials/form');

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
        $postData = (array)$this->getRequest()->getPost();
        if (isset($postData['action']) && $postData['action'] == 'Create operator') {
            return $this->redirectToRoute('create_operator');
        }
        if (isset($postData['action']) && $postData['action'] == 'Create transport manager') {
            return $this->redirectToRoute('create_transport_manager');
        }
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
