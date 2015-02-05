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

        $container = new Container('search');
        $headerSearch->bind($container);

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer('headerSearch')
            ->set($headerSearch);
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

        return $this;
    }

    /**
     * Set ViewHelperManager
     * @param mixed $viewHelperManager
     */
    public function setViewHelperManager($viewHelperManager)
    {
        $this->viewHelperManager = $viewHelperManager;
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
    }

    /**
     * Get FormAnnotationBuilder
     * @return \Common\Service\FormAnnotationBuilderFactory
     */
    public function getFormAnnotationBuilder()
    {
        return $this->formAnnotationBuilder;
    }
}
