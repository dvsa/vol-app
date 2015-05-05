<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;

/**
 * Class Organisation
 * @package Olcs\Listener\RouteParam
 */
class Organisation implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;
    use ServiceLocatorAwareTrait;

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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'organisation', [$this, 'onOrganisation'], 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onOrganisation(RouteParam $e)
    {
        $organisationEntityService = $this->getServiceLocator()->get('Entity\Organisation');
        $isIrfo = $organisationEntityService->isIrfo($e->getValue());

        if (!$isIrfo) {
            // hide IRFO navigation
            $navigationPlugin = $this->getViewHelperManager()->get('Navigation')->__invoke('navigation');
            $navigationPlugin->findById('operator_irfo')->setVisible(false);
        }
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setServiceLocator($serviceLocator);

        return $this;
    }
}
