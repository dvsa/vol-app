<?php

namespace Olcs\Listener;

use Common\Service\Data\Search\Search as SearchService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Controller\SearchController;
use Olcs\Form\Element\SearchOrderFieldset;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Form\FormElementManager;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Session\Container;
use ZfcRbac\Identity\IdentityProviderInterface;

/**
 * Class HeaderSearch
 * @package Olcs\Listener
 */
class HeaderSearch implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * ViewHelperManager
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

    /** @var \Common\Service\Helper\FormHelperService */
    private $hlpForm;

    /**
     * @var IdentityProviderInterface
     */
    protected $authenticationService;

    /** @var TranslationHelperService $translator
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

        /** @var User $identity */
        $identity = $this->authenticationService->getIdentity();
        $userData = $identity->getUserData();

        //prevent this from running if the user is not logged in
        if (isset($userData['dataAccess']['allowedSearchIndexes'])) {
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
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return HeaderSearch
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setSearchService($serviceLocator->get('DataServiceManager')->get(SearchService::class));
        $this->setFormElementManager($serviceLocator->get('FormElementManager'));
        $this->translator = $serviceLocator->get('Helper\Translation');
        $this->hlpForm = $serviceLocator->get('Helper\Form');
        $this->authenticationService = $serviceLocator->get(IdentityProviderInterface::class);

        return $this;
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
}
