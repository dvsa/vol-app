<?php

/**
 * Search Controller
 */
namespace Olcs\Controller\Search;

use Olcs\View\Model\Dashboard;
use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Olcs\Form\Model\Form\SimpleSearch;
use Common\Controller\Traits\ViewHelperManagerAware;
use Common\Service\Data\Search\SearchType;
use Common\Service\Data\Search\Search;

/**
 * Search Controller
 */
class SearchController extends AbstractController
{
    use ViewHelperManagerAware;

    public function getIncomingSearchData()
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

        // added this line as a quick fix for broken UT
        $incomingParameters['search'] = isset($incomingParameters['search']) ? $incomingParameters['search'] : '';

        /**
         * Now remove all the data we don't want in the query string.
         */
        $incomingParameters = array_diff_key($incomingParameters, array_flip($remove));

        return $incomingParameters;
    }

    public function searchAction()
    {
        /** @var \Common\Controller\Plugin\ElasticSearch $elasticSearch */
        $elasticSearch = $this->ElasticSearch();

        //$elasticSearch->getFiltersForm();
        $elasticSearch->processSearchData();

        $view = new ViewModel();

        $view = $elasticSearch->generateNavigation($view);
        $view = $elasticSearch->generateResults($view);

        return $this->renderView($view, 'Search results');
    }

    public function generateResults($view)
    {
        $data = $this->getSearchForm()->getObject();
        $data['index'] = $this->getController()->params()->fromRoute('index');

        $this->getSearchService()->setQuery($this->getRequest()->getQuery())
            ->setRequest($this->getRequest())
            ->setIndex($data['index'])
            ->setSearch($data['search']);

        $view->results = $this->getSearchService()->fetchResultsTable();

        $layout = 'layout/' . $this->getLayoutTemplate();
        $view->setTemplate($layout);

        return $view;
    }

    /**
     * @return Search
     */
    public function getSearchService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get(Search::class);
    }

    /**
     * @return SearchType
     */
    public function getSearchTypeService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get(SearchType::class);
    }

    /**
     * Search index action
     *
     * There should probably be a search box on this page I expect.
     */
    public function indexAction()
    {
        /** @var \Zend\Form\Form $form */
        $form = $this->getForm(SimpleSearch::class);

        if ($this->getRequest()->isPost()) {

            $sd = $this->getIncomingSearchData();

            $form->setData($sd);

            if ($form->isValid()) {
                /**
                 * Remove the "index" key from the incoming parameters.
                 */
                $index = $sd['index'];
                unset($sd['index']);

                return $this->redirect()->toRoute(
                    'search',
                    ['index' => $index, 'action' => 'search'],
                    ['query' => $sd, 'code' => 303],
                    true
                );
            }
        }

        $form->get('index')->setValue($this->params()->fromRoute('index'));

        $view = new ViewModel(['searchForm' => $form]);
        $view->setTemplate('search/index-' . $this->params()->fromRoute('index') . '.phtml');
        return $view;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getForm($name)
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
}
