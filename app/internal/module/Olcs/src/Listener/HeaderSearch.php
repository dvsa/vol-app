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
use \Olcs\Service\Data\Search\Search as SearchService;
use Zend\Form\FormElementManager as FormElementManager;

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
            //$this->addFilterFieldsToForm($searchFilterForm);
            $fs = $this->getFormElementManager()->get('SearchFilterFieldset', ['index' => $index, 'name' => 'filter']);
            $searchFilterForm->add($fs);
        }

        $container = new Container('search');
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
     * @param $keys
     * @param $values
     *
     * @return array
     */
    protected function formatFilterOptionsList($keys, $values)
    {
        return array_combine($keys, $values);
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
        $this->setSearchService($serviceLocator->get('DataServiceManager')->get('Olcs\Service\Data\Search\Search'));
        $this->setFormElementManager($serviceLocator->get('FormElementManager'));

        return $this;
    }

    /**
     * Set ViewHelperManager
     * @param mixed $viewHelperManager
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
     * @param FormAnnotationBuilderFactory $formAnnotationBuilder
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
     */
    public function setSearchService(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * @return FormElementManager
     */
    public function getFormElementManager()
    {
        return $this->formElementManager;
    }

    /**
     * @param FormElementManager $formElementManager
     */
    public function setFormElementManager(FormElementManager $formElementManager)
    {
        $this->formElementManager = $formElementManager;
    }
}
