<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Navigation\PluginManager as ViewHelperManager;
use Olcs\Service\Marker\BusRegMarkers;
use Common\Service\Data\BusReg as BusRegService;

/**
 * Class BusRegMarker
 * @package Olcs\Listener\RouteParam
 */
class BusRegMarker implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * @var BusRegMarkers
     */
    protected $busRegMarkerService;

    /**
     * @var ViewHelperManager
     */
    protected $viewHelperManager;

    /**
     * @var BusRegService
     */
    protected $busRegService;

    /**
     * @param \Zend\View\Helper\Navigation\PluginManager $viewHelperManager
     * @return $this
     */
    public function setViewHelperManager($viewHelperManager)
    {
        $this->viewHelperManager = $viewHelperManager;
        return $this;
    }

    /**
     * @return \Zend\View\Helper\Navigation\PluginManager
     */
    public function getViewHelperManager()
    {
        return $this->viewHelperManager;
    }

    /**
     * @param \Olcs\Service\Marker\BusRegMarkers $busRegMarkerService
     * @return $this
     */
    public function setBusRegMarkerService($busRegMarkerService)
    {
        $this->busRegMarkerService = $busRegMarkerService;
        return $this;
    }

    /**
     * @return \Olcs\Service\Marker\BusRegMarkers
     */
    public function getBusRegMarkerService()
    {
        return $this->busRegMarkerService;
    }

    /**
     * @param \Common\Service\Data\BusReg $busRegService
     */
    public function setBusRegService($busRegService)
    {
        $this->busRegService = $busRegService;
    }

    /**
     * @return \Common\Service\Data\BusReg
     */
    public function getBusRegService()
    {
        return $this->busRegService;
    }

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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'busRegId', [$this, 'onBusRegMarker'], 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onBusRegMarker(RouteParam $e)
    {
        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $busReg = $this->getBusRegService()->fetchOne($e->getValue());
        $markers = $this->getBusRegMarkerService()->generateMarkerTypes(['busReg'], ['busReg' => $busReg]);

        $placeholder->getContainer('markers')->set($markers);
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
        $this->setBusRegService($serviceLocator->get('DataServiceManager')->get('Common\Service\Data\BusReg'));

        $busRegMarkerService = $serviceLocator
            ->get('Olcs\Service\Marker\MarkerPluginManager')
            ->get('Olcs\Service\Marker\BusRegMarkers');

        $this->setBusRegMarkerService($busRegMarkerService);
        return $this;
    }
}
