<?php

namespace Olcs\Controller;

use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Session\Container;
use Laminas\View\Helper\Placeholder;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;
use LmcRbacMvc\Exception\UnauthorizedException;
use LmcRbacMvc\Service\RoleService;

/**
 * Main search controller
 *
 * @method \Common\Controller\Plugin\ElasticSearch elasticSearch()
 */
class SearchController extends AbstractController implements LeftViewProvider
{
    use \Common\Controller\Lva\Traits\CrudActionTrait;

    protected $navigationId = 'mainsearch';

    public const CONTAINER = 'searchForm';

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected $navigation;
    protected RoleService $roleService;
    protected Placeholder $placeholder;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        FlashMessengerHelperService $flashMessengerHelper,
        $navigation,
        RoleService $roleService,
        Placeholder $placeholder
    ) {
        parent::__construct($scriptFactory, $formHelper, $tableFactory, $viewHelperManager);
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->navigation = $navigation;
        $this->roleService = $roleService;
        $this->placeholder = $placeholder;
    }

    /**
     * utility method to get placeholder dependency in controller plugins
     *
     * @return Placeholder
     */
    public function getPlaceholder(): Placeholder
    {
        return $this->placeholder;
    }

    /**
     * At first glance this seems a little unnecessary, but we need to intercept the post
     * and turn it into a get. This way the search URL contains the search params.
     *
     * @return \Laminas\Http\Response
     */
    public function postAction()
    {
        $sd = $this->ElasticSearch()->getSearchData();

        $container = new Container(self::CONTAINER);
        $container->search = $sd['search'];
        $container->index = $sd['index'];

        /**
         * Remove the "index" key from the incoming parameters.
         */
        $index = $sd['index'];
        unset($sd['index']);
        // unset the page param, as when filtering or new search the pagination should be on page 1
        unset($sd['page']);

        return $this->redirect()->toRoute(
            'search',
            ['index' => $index, 'action' => 'search'],
            ['query' => $sd, 'code' => 303],
            true
        );
    }

    /**
     * Back action
     *
     * @return \Laminas\Http\Response
     */
    public function backAction()
    {
        $sd = $this->ElasticSearch()->getSearchData();

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

    /**
     * Index action
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        return $this->backAction();
    }

    /**
     * Search action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function searchAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->handleCrudAction($this->params()->fromPost());
        }

        /**
         * @var \Common\Controller\Plugin\ElasticSearch $elasticSearch
        */
        $elasticSearch = $this->ElasticSearch();

        $searchIndex = $elasticSearch->getSearchData()['index'];
        if (!$this->canAccessSearchIndex($searchIndex)) {
            throw new UnauthorizedException("User not allowed to access ${searchIndex} search index");
        }

        $this->loadScripts(['table-actions']);

        $form = $elasticSearch->getFiltersForm();
        $elasticSearch->processSearchData();

        $view = new ViewModel();
        $view->setTemplate('sections/search/pages/results');

        // make all elements not required
        foreach ($form->getInputFilter()->get('filter')->getInputs() as $input) {
            /* @var $input \Laminas\InputFilter\Input */
            $input->setRequired(false);
        }

        // make all elements not required
        foreach ($form->getInputFilter()->get('sort')->getInputs() as $input) {
            /* @var $input \Laminas\InputFilter\Input */
            $input->setRequired(false);
        }

        // if valid then generate results
        if ($form->isValid()) {
            $excludeNavIds = ['search-irfo'];

            if ($this->currentUser()->getUserData()['dataAccess']['isIrfo']) {
                $excludeNavIds = [];
            }
            if (
                !$this->currentUser()->getUserData()['dataAccess']['canAccessAll']
                && $this->currentUser()->getUserData()['dataAccess']['canAccessNi']
            ) {
                $excludeNavIds = ['search-psv_disc', 'search-bus_reg', ...$excludeNavIds];
            }
            $elasticSearch->configureNavigation($excludeNavIds);
            $view = $elasticSearch->generateResults($view);
        }

        return $this->renderView($view, 'Search results');
    }

    /**
     * Reset action
     *
     * @return \Laminas\Http\Response
     */
    public function resetAction()
    {
        /**
 * @var \Common\Controller\Plugin\ElasticSearch $elasticSearch
*/
        $elasticSearch = $this->ElasticSearch();

        $sd = $elasticSearch->getSearchData();

        $elasticSearch->resetSearchSession($sd['search']);

        return $this->redirect()->toRoute(
            'search',
            ['index' => $sd['index'], 'action' => 'search'],
            ['query' => ['search' => $sd['search']], 'code' => 303],
            true
        );
    }

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/search/partials/left');

        return $view;
    }

    /**
     * Remove Vehicle Section 26 marker
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    protected function vehicleremove26Action()
    {
        if ($this->getRequest()->isPost()) {
            $ids = explode(',', $this->params('child_id'));
            $response = $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Vehicle\UpdateSection26::create(
                    ['ids' => $ids, 'section26' => 'N']
                )
            );
            if ($response->isOk()) {
                $this->flashMessengerHelper
                    ->addSuccessMessage('form.vehicle.removeSection26.success');
            } else {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }
            return $this->redirect()->toRouteAjax('search', array('index' => 'vehicle', 'action' => 'search'));
        }

        $formHelper = $this->formHelper;
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $this->getRequest());
        $form->get('messages')->get('message')->setValue('form.vehicle.removeSection26.confirm');

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Remove section 26');
    }

    /**
     * Set Vehicle Section 26 marker
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    protected function vehicleset26Action()
    {
        if ($this->getRequest()->isPost()) {
            $ids = explode(',', $this->params('child_id'));
            $response = $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Vehicle\UpdateSection26::create(
                    ['ids' => $ids, 'section26' => 'Y']
                )
            );
            if ($response->isOk()) {
                $this->flashMessengerHelper
                    ->addSuccessMessage('form.vehicle.setSection26.success');
            } else {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }
            return $this->redirect()->toRouteAjax('search', array('index' => 'vehicle', 'action' => 'search'));
        }

        $formHelper = $this->formHelper;
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $this->getRequest());
        $form->get('messages')->get('message')->setValue('form.vehicle.setSection26.confirm');

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Remove section 26');
    }

    /**
     * Process the search
     *
     * @param array $data Data
     *
     * @return \Laminas\Http\Response
     */
    public function processSearch($data)
    {
        $data = array_merge($data['search'], $data['search-advanced']);

        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }

        /**
         * @NOTE (RC) added data to query string rather than route params as data contained a nested array which was
         * causing an error in zf2 url builder. I am informed by (CR) that this advanced search is disappearing soon
         * anyway
         */
        $url = $this->url()->fromRoute('operators/operators-params', [], array('query' => $data));

        return $this->redirect()->toUrl($url);
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
        $navigation = $this->navigation;
        if (!empty($this->navigationId)) {
            $navigation->findOneBy('id', $this->navigationId)->setActive();
        }

        return true;
    }

    /**
     * @param  string $searchIndex
     * @return bool
     */
    protected function canAccessSearchIndex(string $searchIndex): bool
    {
        $roleService = $this->roleService;
        if ($searchIndex === 'user' && $roleService->matchIdentityRoles([RefData::ROLE_INTERNAL_LIMITED_READ_ONLY])) {
            return false;
        }

        return true;
    }
}
