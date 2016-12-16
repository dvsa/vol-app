<?php

namespace Olcs\Listener;

use Common\Service\FormAnnotationBuilderFactory;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\Placeholder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use \Common\Form\Annotation\CustomAnnotationBuilder;
use Zend\Session\Container;
use Common\Service\Data\Search\Search as SearchService;
use Zend\Form\FormElementManager as FormElementManager;
use Olcs\Form\Element\SearchOrderFieldset;

/**
 * Class HeaderSearch
 * @package Olcs\Listener
 */
class HeaderSearch implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * Form annotation builder service
     * @var
     */
    protected $formAnnotationBuilder;

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

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), 20);
    }

    /**
     * @param MvcEvent $e
     */
    public function onDispatch(MvcEvent $e)
    {
        $class = 'Olcs\\Form\\Model\\Form\\HeaderSearch';
        $headerSearch = $this->getFormAnnotationBuilder()->createForm($class);

        $searchFilterFormName = 'Olcs\\Form\\Model\\Form\\SearchFilter';
        /** @var \Common\Form\Form $searchFilterForm */
        $searchFilterForm = $this->getFormAnnotationBuilder()->createForm($searchFilterFormName);
        $searchFilterForm->remove('csrf');

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

        $key = md5('global_search' . '_' . str_replace(' ', '', $index));

        $container = new Container($key);
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
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setFormAnnotationBuilder($serviceLocator->get('FormAnnotationBuilder'));
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setSearchService($serviceLocator->get('DataServiceManager')->get(SearchService::class));
        $this->setFormElementManager($serviceLocator->get('FormElementManager'));

        return $this;
    }

    /**
     * Set ViewHelperManager
     *
     * @param mixed $viewHelperManager
     * @return HeaderSearch
     */
    public function setViewHelperManager($viewHelperManager)
    {
        $this->viewHelperManager = $viewHelperManager;
        return $this;
    }

    /**
     * GetViewHelperManager
     * @return mixed
     */
    public function getViewHelperManager()
    {
        return $this->viewHelperManager;
    }

    /**
     * Set FormAnnotationBuilder
     *
     * @param \Common\Form\Annotation\CustomAnnotationBuilder $formAnnotationBuilder
     * @return HeaderSearch
     */
    public function setFormAnnotationBuilder(CustomAnnotationBuilder $formAnnotationBuilder)
    {
        $this->formAnnotationBuilder = $formAnnotationBuilder;
        return $this;
    }

    /**
     * Get FormAnnotationBuilder
     * @return \Common\Service\FormAnnotationBuilderFactory
     */
    public function getFormAnnotationBuilder()
    {
        return $this->formAnnotationBuilder;
    }

    /**
     * @return SearchService
     */
    public function getSearchService()
    {
        return $this->searchService;
    }

    /**
     * @param SearchService $searchService
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
     * @param FormElementManager $formElementManager
     * @return HeaderSearch
     */
    public function setFormElementManager(FormElementManager $formElementManager)
    {
        $this->formElementManager = $formElementManager;
        return $this;
    }
}
