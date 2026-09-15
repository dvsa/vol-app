<?php

namespace Common\Controller\Plugin;

use Common\Controller\Traits\GenericMethods;
use Common\Service\Data\Search\Search;
use Common\Service\Data\Search\SearchType;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Navigation\Navigation;
use Laminas\Session\Container;
use Laminas\View\Model\ViewModel;

/**
 * Class ElasticSearch - Generates and processes calls to Elastic Search
 *
 * @method FlashMessenger flashMessenger()
 * @method GenericMethods redirectToRoute($route = null, $params = [], $options = [], $reuse = false)
 */
class ElasticSearch extends AbstractPlugin
{
    public $navigationId;
    /**
     * Session container name
     * @var string
     */
    private $containerName;

    /**
     * Search Term
     * @var string
     */
    private $searchTerm;

    /**
     * Search Data
     * @var array
     */
    private $searchData;

    /**
     * Search type service
     * @var SearchType
     */
    protected $searchTypeService;

    /**
     * Search service
     * @var Search
     */
    protected $searchService;

    /**
     * Navigation service
     * @var Navigation
     */
    protected $navigationService;

    /**
     * Page route to determine where forms should post and redirect to
     * @var string
     */
    private $pageRoute;

    /**
     * Invokes the plugin
     *
     * @param array $options
     * @return $this
     */
    public function __invoke($options = [])
    {
        $containerName = $options['container_name'] ?? 'global_search';

        if (isset($options['page_route'])) {
            $pageRoute = $options['page_route'];
        } else {
            $pageRoute = $this->getController()->getEvent()->getRouteMatch()->getMatchedRouteName();
        }

        $this->setContainerName($containerName);
        $this->setPageRoute($pageRoute);

        $this->setSearchData($this->extractSearchData());

        return $this;
    }

    /**
     * At first glance this seems a little unnecessary, but we need to intercept the post
     * and turn it into a get. This way the search URL contains the search params.
     */
    public function postAction()
    {
        $sd = $this->getSearchData();

        /**
         * Remove the "index" key from the incoming parameters.
         */
        $index = $sd['index'];
        unset($sd['index']);

        return $this->getController()->redirect()->toRoute(
            $this->getPageRoute(),
            ['index' => $index, 'action' => 'search'],
            ['query' => $sd, 'code' => 303],
            true
        );
    }

    public function backAction()
    {
        $sd = $this->getSearchData();

        /**
         * Remove the "index" key from the incoming parameters.
         */
        $index = $sd['index'];
        unset($sd['index']);

        return $this->getController()->redirect()->toRoute(
            'search',
            ['index' => $index, 'action' => 'search'],
            ['query' => $sd, 'code' => 303],
            true
        );
    }

    public function processSearchData(): void
    {
        $incomingParameters = [];

        if ($routeParams = $this->getController()->params()->fromRoute()) {
            $incomingParameters += $routeParams;
        }

        if ($postParams = $this->getController()->params()->fromPost()) {
            $incomingParameters += $postParams;
        }

        if (($queryParams = (array) $this->getController()->getRequest()->getQuery()) !== []) {
            $incomingParameters = array_merge($incomingParameters, $queryParams);
        }

        //there are multiple places search data can come from:
        //route, query, post and session

        //there are lots of params we are interested in:
        //filters, index, search query, page, limit

        //a post request can come from two forms a) the filter form, b) the query form
        $form = $this->getSearchForm();
        $form->setData($incomingParameters);

        if ($form->isValid()) {
            //save to session, reset filters in session...
            //get index from post as well, override what is in the route match
            $data = $form->getData();
            $this->getController()->getEvent()->getRouteMatch()->setParam('index', $data['index']);
        }

        $data = $this->getSearchForm()->getObject();

        //update data with information from route, and rebind to form so that form data is correct
        $data['index'] = $this->getController()->params()->fromRoute('index');
        $this->getSearchForm()->setData($data);
    }

    /**
     * Returns the header search form
     * This is Olcs\Form\Model\Form\HeaderSearch from within olcs-internal,
     * so we can't use a return type due to static analysis
     */
    public function getSearchForm()
    {
        return $this->getController()->getPlaceholder()
            ->getContainer('headerSearch')
            ->getValue();
    }

    /**
     * Returns the search filter form.
     * This is Olcs\Form\Model\Form\SearchFilter from within olcs-internal,
     * so we can't use a return type due to static analysis
     */
    public function getFiltersForm()
    {
        /** @var \Laminas\Form\Form $form */
        $form = $this->getController()->getPlaceholder()
            ->getContainer('searchFilter')
            ->getValue();

        $sd = $this->getSearchData();

        $url = $this->getController()->url()->fromRoute(
            $this->getPageRoute(),
            ['index' => $sd['index'], 'action' => 'post'],
            ['query' => ['search' => $sd['search']]]
        );

        $form->setAttribute('action', $url);
        $form->setData($sd);

        return $form;
    }

    public function searchAction()
    {
        $sd = $this->getSearchData();

        $this->getFiltersForm();
        $data = $this->getSearchForm()->getObject();
        //override with get route index unless request is post

        $this->processSearchData();

        //update data with information from route, and rebind to form so that form data is correct
        $data['index'] = $this->getController()->params()->fromRoute('index');
        $this->getSearchForm()->setData($data);

        if (empty($data['search'])) {
            $this->flashMessenger()->addErrorMessage('Please provide a search term');
            return $this->redirectToRoute('dashboard');
        }

        $this->getSearchService()->setQuery($this->getController()->getRequest()->getQuery())
            ->setRequest($this->getController()->getRequest())
            ->setIndex($data['index'])
            ->setSearch($data['search']);

        $view = new ViewModel();
        $view->setTemplate('sections/search/pages/results');

        $view->indexes = $this->getSearchTypeService()->getNavigation('internal-search', ['search' => $sd['search']]);
        $view->results = $this->getSearchService()->fetchResultsTable();

        return $this->getController()->renderView($view, 'Search results');
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
        if (!empty($this->navigationId)) {
            $this->getNavigationService()->findOneBy('id', $this->navigationId)->setActive();
        }

        return true;
    }

    public function extractSearchData(): array
    {
        $remove = [
            'controller',
            'action',
            'module',
            'submit'
        ];

        $incomingParameters = [];

        if ($routeParams = $this->getController()->params()->fromRoute()) {
            $incomingParameters += $routeParams;
        }

        if (($queryParams = (array) $this->getController()->params()->fromQuery()) !== []) {
            $incomingParameters = array_merge($incomingParameters, $queryParams);
        }

        if (($postParams = (array) $this->getController()->params()->fromPost()) !== []) {
            $incomingParameters = array_merge($incomingParameters, $postParams);
        }

        // added this line as a quick fix for broken UT
        $incomingParameters['search'] ??= '';

        $this->setSearchTerm($incomingParameters['search']);

        /**
         * Now remove all the data we don't want in the query string.
         */
        $incomingParameters = array_diff_key($incomingParameters, array_flip($remove));

        return $incomingParameters;
    }

    public function configureNavigation($removeNavIds = []): void
    {
        $sd = $this->getSearchData();

        $nav = $this->getSearchTypeService()->getNavigation('internal-search', ['search' => $sd['search']]);
        // A little workaround to set the current nav as active
        $nav->findOneBy('id', 'search-' . $sd['index'])->setActive(true);

        foreach ($removeNavIds as $navId) {
            $nav->removePage($nav->findOneBy('id', $navId), true);
        }

        $this->getController()->getPlaceholder()
            ->getContainer('horizontalNavigationContainer')
            ->set($nav);
    }

    public function generateResults($view)
    {
        $data = $this->getSearchForm()->getObject();
        $data['index'] = $this->getController()->params()->fromRoute('index');

        $this->getSearchService()->setQuery($this->getController()->getRequest()->getQuery())
            ->setRequest($this->getController()->getRequest())
            ->setIndex($data['index'])
            ->setSearch($data['search']);

        $view->results = $this->getSearchService()->fetchResultsTable();
        $view->setTemplate('sections/search/pages/results');

        return $view;
    }

    public function setContainerName($containerName)
    {
        $this->containerName = $containerName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContainerName()
    {
        return $this->containerName;
    }

    /**
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @param string $searchTerm
     */
    public function setSearchTerm($searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }

    public function setSearchData(mixed $searchData): static
    {
        $this->searchData = $searchData;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSearchData()
    {
        return $this->searchData;
    }

    /**
     * @param string $pageRoute
     */
    public function setPageRoute($pageRoute): static
    {
        $this->pageRoute = $pageRoute;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageRoute()
    {
        return $this->pageRoute;
    }

    /**
     * @param Search $searchService
     * @return ElasticSearch
     */
    public function setSearchService($searchService)
    {
        $this->searchService = $searchService;
        return $this;
    }

    /**
     * @return Search
     */
    public function getSearchService()
    {
        return $this->searchService;
    }

    /**
     * @param SearchType $searchTypeService
     * @return ElasticSearch
     */
    public function setSearchTypeService($searchTypeService)
    {
        $this->searchTypeService = $searchTypeService;
        return $this;
    }

    /**
     * @return SearchType
     */
    public function getSearchTypeService()
    {
        return $this->searchTypeService;
    }

    /**
     * @param Navigation $navigationService
     */
    public function setNavigationService($navigationService): void
    {
        $this->navigationService = $navigationService;
    }

    /**
     * @return Navigation
     */
    public function getNavigationService()
    {
        return $this->navigationService;
    }

    public function resetSearchSession($term): void
    {
        // A bit fudgy way to clear session container.

        $key = md5($this->getContainerName() . '_' . str_replace(' ', '', $this->getSearchTerm()));
        $container = new Container($key);
        $container->exchangeArray(['search' => $term]);
    }
}
