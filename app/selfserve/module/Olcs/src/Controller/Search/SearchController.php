<?php

/**
 * Search Controller
 */
namespace Olcs\Controller\Search;

use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Olcs\Form\Model\Form\SearchOperator;
use Olcs\Form\Model\Form\SimpleSearch;
use Common\Controller\Traits\ViewHelperManagerAware;
use Common\Service\Data\Search\SearchType;
use Common\Service\Data\Search\Search;
use Olcs\Form\Model\Form\SearchFilter as SearchFilterForm;
use Olcs\Form\Element\SearchFilterFieldset;
use Olcs\Form\Element\SearchDateRangeFieldset;
use Olcs\Form\Element\SearchOrderFieldset;
use Zend\Session\Container;

/**
 * Search Controller
 */
class SearchController extends AbstractController
{
    use ViewHelperManagerAware;

    /**
     * Search index action
     *
     * There should probably be a search box on this page I expect.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $index = $this->params()->fromRoute('index');

        if (empty($index)) {
            // show index page if index empty
            $view = new ViewModel();
            $view->setTemplate('search/index');
            return $view;
        }

        /** @var \Zend\Form\Form $form */
        $form = $this->getFormForIndex($index);

        if ($this->getRequest()->isPost()) {
            $sd = $this->getIncomingSearchData();

            $form->setData($sd);

            if ($form->isValid()) {
                /**
                 * Remove the "index" key from the incoming parameters.
                 */
                $index = $sd['index'];
                unset($sd['index']);

                if (!empty($sd['searchBy'])) {
                    $index = $this->getIndexForSearchBy($sd['searchBy']);
                }

                return $this->redirect()->toRoute(
                    'search',
                    ['index' => $index, 'action' => 'search'],
                    ['query' => $sd, 'code' => 303],
                    true
                );
            }
        }

        $form->get('index')->setValue($index);

        $view = new ViewModel(['searchForm' => $form]);
        $view->setTemplate('search/index-' . $index . '.phtml');

        $this->placeholder()->setPlaceholder('usePageTitleAsHeader', true);

        return $view;
    }

    /**
     * Get index for data
     *
     * @param string $searchBy Search by
     *
     * @return string|null
     */
    private function getIndexForSearchBy($searchBy)
    {
        // searchBy to index mapping
        $mapping = [
            'address' => 'operating-centre',
            'business' => 'operator',
            'licence' => 'operator',
            'person' => 'person',
        ];

        return isset($mapping[$searchBy]) ? $mapping[$searchBy] : null;
    }

    /**
     * Get incoming search data to generate the search url
     *
     * @return array|mixed
     */
    private function getIncomingSearchData()
    {
        $remove = [
            'controller',
            'action',
            'module',
            'submit'
        ];

        $incomingParameters = [];

        if ($routeParams = $this->params()->fromRoute()) {
            $incomingParameters += $routeParams;
        }

        if ($queryParams = (array) $this->params()->fromQuery()) {
            $incomingParameters = array_merge($incomingParameters, $queryParams);
        }

        if ($postParams = (array) $this->params()->fromPost()) {
            $incomingParameters = array_merge($incomingParameters, $postParams);
        }

        $search = '';

        if (isset($incomingParameters['search']) && $incomingParameters['search'] != '') {
            $search = $incomingParameters['search'];
        }

        // if "search" is an array within text on the filter form ... throws undefined index error otherwise
        if (isset($incomingParameters['text']) && isset($incomingParameters['text']['search'])) {

            $search = $incomingParameters['text']['search'];
        }

        $incomingParameters['search'] = $search;
        $incomingParameters['text']['search'] = $search;

        $this->storeSearchUrl($routeParams, $queryParams);

        /**
         * Now remove all the data we don't want in the query string.
         */
        $incomingParameters = array_diff_key($incomingParameters, array_flip($remove));

        return $incomingParameters;
    }

    /**
     * Store search params in the session to generate 'Back to search results' links
     * Taken from route params and query params stored in the session
     *
     * @param array $routeParams Route params
     * @param array $queryParams Query params
     *
     * @return void
     */
    private function storeSearchUrl($routeParams, $queryParams)
    {
        $sessionSearch = new Container('searchQuery');

        $sessionSearch->route = 'search';
        $sessionSearch->routeParams = $routeParams;
        $sessionSearch->queryParams = $queryParams;
    }

    /**
     * Search action
     *
     * @return ViewModel
     */
    public function searchAction()
    {
        $indexPrm = $this->params()->fromRoute('index');

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        //  get search criteria
        $data = $this->getIncomingSearchData();
        $data['index'] = $indexPrm;

        //  define search filter form
        $form = $this->initialiseFilterForm();
        $form->get('index')->setValue($indexPrm);

        $searchPostUrl = $this->url()->fromRoute('search', ['index' => $indexPrm, 'action' => 'index'], [], true);
        $form->setAttribute('action', $searchPostUrl);

        //  request data from API and build table of results
        $this->getSearchService()->setQuery($request->getQuery())
            ->setRequest($request)
            ->setIndex($data['index'])
            ->setSearch($data['search']);

        $view = new ViewModel(
            [
                'index' => $indexPrm,
                'backRoute' => $this->getBackRoute($indexPrm)
            ]
        );
        $view->setTemplate('layouts/main-search-results.phtml');

        $view->results = $this->getSearchService()->fetchResultsTable();

        if ($view->results->getTotal() === 0) {
            $view->noResultsMessage = 'search-no-results';
        }

        $this->getServiceLocator()->get('Script')->loadFile('search-results');

        $this->placeholder()->setPlaceholder('pageTitle', 'page.title.search-'.$indexPrm.'.index');
        $this->placeholder()->setPlaceholder('usePageTitleAsHeader', true);

        return $view;
    }

    /**
     * Get back route name
     *
     * @param string $index Index
     *
     * @return string
     */
    private function getBackRoute($index)
    {
        // index to back route mapping
        $mapping = [
            'operating-centre' => 'search-operator',
            'operator' => 'search-operator',
            'person' => 'search-operator',
            'publication' => 'search-publication',
            'bus' => 'search-bus',
            'vehicle-external' => 'search-vehicle-external',
        ];

        return isset($mapping[$index]) ? $mapping[$index] : 'search';
    }

    /**
     * Generate the search form for index page
     *
     * @param string $index Index name
     *
     * @return mixed
     */
    private function getFormForIndex($index)
    {
        $formName = ($index === 'operator') ? SearchOperator::class : SimpleSearch::class;

        $serviceLocator = $this->getServiceLocator();

        /** @var \Zend\Form\Form $form */
        $form = $serviceLocator->get('Helper\Form')->createForm($formName);
        $serviceLocator->get('Helper\Form')->setFormActionFromRequest($form, $this->getRequest());

        if ($formName === SearchOperator::class) {
            $translator = $serviceLocator->get('Helper\Translation');

            $form->get('search')->setLabelAttributes(
                [
                    'data-search-address' => $translator->translate('search.operator.field.search.address.label'),
                    'data-search-business' => $translator->translate('search.operator.field.search.business.label'),
                    'data-search-licence' => $translator->translate('search.operator.field.search.licence.label'),
                    'data-search-person' => $translator->translate('search.operator.field.search.person.label'),
                ]
            );

            $serviceLocator->get('Script')->loadFile('search-operator');
        } else {
            // OLCS-13903 set custom hints depending on the search being performed
            $form->get('search')->setLabel('search.form.label.' . $index);
        }

        return $form;
    }

    /**
     * Generate filter form
     *
     * @param string $name Form name
     *
     * @return mixed
     */
    public function getFilterForm($name)
    {
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm($name);
        $this->getServiceLocator()->get('Helper\Form')->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Sets the navigation to that secified in the controller. Useful for when a controller is
     * 100% reresented by a single navigation object.
     *
     * @see $this->navigationId
     *
     * @return boolean true
     */
    public function setNavigationCurrentLocation()
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        if (!empty($this->navigationId)) {
            $navigation->findOneBy('id', $this->navigationId)->setActive();
        }

        return true;
    }

    /**
     * Initialise the filter form
     *
     * @return \Common\Form\Form
     */
    private function initialiseFilterForm()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getFilterForm(SearchFilterForm::class);

        // Index is required for filter fields as they are index specific.
        $index = $this->params()->fromRoute('index');

        if (isset($index)) {

            $this->getSearchService()->setIndex($index);

            // terms filters
            /** @var  $fs */
            $fs = $this->getServiceLocator()->get('FormElementManager')
                ->get(SearchFilterFieldset::class, ['index' => $index, 'name' => 'filter']);
            $form->add($fs);

            // date ranges
            $fs = $this->getServiceLocator()->get('FormElementManager')
                ->get(SearchDateRangeFieldset::class, ['index' => $index, 'name' => 'dateRanges']);
            $form->add($fs);

            // order
            $fs = $this->getServiceLocator()->get('FormElementManager')
                ->get(SearchOrderFieldset::class, ['index' => $index, 'name' => 'sort']);
            $form->add($fs);
        }

        $form->populateValues($this->getIncomingSearchData());

        $this->getServiceLocator()
            ->get('ViewHelperManager')
            ->get('placeholder')
            ->getContainer('searchFilter')
            ->set($form);

        // OLCS-13903 set custom hints depending on the search being performed
        $form->get('text')->get('search')->setLabel('search.form.label.' . $index);

        return $form;
    }

    /**
     * Get the search service
     *
     * @return Search
     */
    public function getSearchService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get(Search::class);
    }

    /**
     * Get search type service
     *
     * @return SearchType
     */
    public function getSearchTypeService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get(SearchType::class);
    }
}
