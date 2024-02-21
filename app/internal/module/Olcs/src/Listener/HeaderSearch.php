<?php

namespace Olcs\Listener;

use Common\Service\Helper\FormHelperService;
use Psr\Container\ContainerInterface;
use Common\Service\Data\Search\Search as SearchService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Controller\SearchController;
use Olcs\Form\Element\SearchOrderFieldset;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Form\FormElementManager;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\Container;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * Class HeaderSearch
 *
 * @package Olcs\Listener
 */
class HeaderSearch implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * ViewHelperManager
     *
     * @var
     */
    protected $viewHelperManager;

    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * @var FormElementManager
     */
    protected $formElementManager;

    /**
     * @var \Common\Service\Helper\FormHelperService
     */
    private $hlpForm;

    /**
     * @var IdentityProviderInterface
     */
    protected $authenticationService;

    /**
     * @var TranslationHelperService $translator
     */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), 20);
    }

    /**
     * onDispatch
     *
     * @param MvcEvent $e Event
     *
     * @return void
     */
    public function onDispatch(MvcEvent $e)
    {
        $headerSearch = $this->hlpForm->createForm(\Olcs\Form\Model\Form\HeaderSearch::class, false);
        $searchFilterForm = $this->hlpForm->createForm(\Olcs\Form\Model\Form\SearchFilter::class, false);

        $identity = $this->authenticationService->getIdentity();
        $userData = $identity->getUserData();

        //prevent this from running if the user is not logged in
        if (isset($userData['dataAccess']['allowedSearchIndexes'])) {
            // Set Header Search index dropdown with allowed search index values from MyAccount Query
            $headerSearch->get('index')->setValueOptions($userData['dataAccess']['allowedSearchIndexes']);
        }

        // Index is required for filter fields as they are index specific.
        $index = $e->getRouteMatch()->getParam('index');
        if (isset($index)) {
            $this->getSearchService()->setIndex($index);

            // terms filters
            $fs = $this->getFormElementManager()->get('SearchFilterFieldset', ['index' => $index, 'name' => 'filter']);
            $searchFilterForm->add($fs);

            // date ranges
            $fs = $this->getFormElementManager()
                ->get('SearchDateRangeFieldset', ['index' => $index, 'name' => 'dateRanges']);
            $searchFilterForm->add($fs);

            // order
            $fs = $this->getFormElementManager()
                ->get(SearchOrderFieldset::class, ['index' => $index, 'name' => 'sort']);
            $searchFilterForm->add($fs);
        }

        $container = new Container(SearchController::CONTAINER);
        $headerSearch->bind($container);
        $searchFilterForm->bind($container);

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer('headerSearch')
            ->set($headerSearch);

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer('searchFilter')
            ->set($searchFilterForm);
    }

    /**
     * Set ViewHelperManager
     *
     * @param mixed $viewHelperManager View helper manager
     *
     * @return HeaderSearch
     */
    public function setViewHelperManager($viewHelperManager)
    {
        $this->viewHelperManager = $viewHelperManager;
        return $this;
    }

    /**
     * Get ViewHelperManager
     *
     * @return mixed
     */
    public function getViewHelperManager()
    {
        return $this->viewHelperManager;
    }

    /**
     * Get search service
     *
     * @return SearchService
     */
    public function getSearchService()
    {
        return $this->searchService;
    }

    /**
     * Set search service
     *
     * @param SearchService $searchService Search service
     *
     * @return HeaderSearch
     */
    public function setSearchService(SearchService $searchService)
    {
        $this->searchService = $searchService;
        return $this;
    }

    /**
     * Form element manager
     *
     * @return FormElementManager
     */
    public function getFormElementManager()
    {
        return $this->formElementManager;
    }

    /**
     * Set form element manager
     *
     * @param FormElementManager $formElementManager Form element manager
     *
     * @return HeaderSearch
     */
    public function setFormElementManager(FormElementManager $formElementManager)
    {
        $this->formElementManager = $formElementManager;
        return $this;
    }

    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return HeaderSearch
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): HeaderSearch
    {
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        $this->setSearchService($container->get('DataServiceManager')->get(SearchService::class));
        $this->setFormElementManager($container->get('FormElementManager'));
        $this->translator = $container->get(TranslationHelperService::class);
        $this->hlpForm = $container->get(FormHelperService::class);
        $this->authenticationService = $container->get(IdentityProviderInterface::class);
        return $this;
    }
}
