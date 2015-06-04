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

/**
 * Class TransportManagerMarker
 * @package Olcs\Listener\RouteParam
 */
class TransportManagerMarker implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * @var TransportManagerMarker service
     */
    protected $transportManagerMarkerService;

    /**
     * @var ViewHelperManager
     */
    protected $viewHelperManager;

    /**
     * @var TransportManagerService
     */
    protected $transportManagerService;

    /**
     * @var TransportManagerLicenceService
     */
    protected $transportManagerLicenceService;

    /**
     * @var TransportManagerApplicationService
     */
    protected $transportManagerApplicationService;

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
     * @param \Olcs\Service\Marker\TransportManagerMarkers $transportManagerMarkerService
     * @return $this
     */
    public function setTransportManagerMarkerService($transportManagerMarkerService)
    {
        $this->transportManagerMarkerService = $transportManagerMarkerService;
        return $this;
    }

    /**
     * @return \Olcs\Service\Marker\TransportManagerMarkers
     */
    public function getTransportManagerMarkerService()
    {
        return $this->transportManagerMarkerService;
    }

    /**
     * @param \Common\Service\Entity\TransportManager $transportManagerService
     */
    public function setTransportManagerService($transportManagerService)
    {
        $this->transportManagerService = $transportManagerService;
    }

    /**
     * @return \Common\Service\Entity\TransportManager
     */
    public function getTransportManagerService()
    {
        return $this->transportManagerService;
    }

    /**
     * @param \Common\Service\Entity\TransportManagerLicence $transportManagerLicenceService
     */
    public function setTransportManagerLicenceService($transportManagerLicenceService)
    {
        $this->transportManagerLicenceService = $transportManagerLicenceService;
    }

    /**
     * @return \Common\Service\Entity\TransportManagerLicence
     */
    public function getTransportManagerLicenceService()
    {
        return $this->transportManagerLicenceService;
    }

    /**
     * @param \Common\Service\Entity\TransportManagerApplication $transportManagerApplicationService
     */
    public function setTransportManagerApplicationService($transportManagerApplicationService)
    {
        $this->transportManagerApplicationService = $transportManagerApplicationService;
    }

    /**
     * @return \Common\Service\Entity\TransportManagerApplication
     */
    public function getTransportManagerApplicationService()
    {
        return $this->transportManagerApplicationService;
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
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'transportManager',
            [$this, 'onTransportManagerMarker'],
            1
        );
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'licence',
            [$this, 'onLicenceTransportManagerMarker'],
            1
        );
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'application',
            [$this, 'onApplicationTransportManagerMarker'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onTransportManagerMarker(RouteParam $e)
    {
        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $details = $this->getTransportManagerService()->getTmForMarkers($e->getValue());
        $transportManager = ['transportManager' => $details];
        $markers = $this->getTransportManagerMarkerService()
            ->generateMarkerTypes(['transportManager'], ['transportManager' => $transportManager]);

        $placeholder->getContainer('tmMarkers')->set($markers);
    }

    /**
     * @param RouteParam $e
     */
    public function onLicenceTransportManagerMarker(RouteParam $e)
    {
        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $details = $this->getTransportManagerLicenceService()->getTmForLicence($e->getValue())['Results'];
        $transportManagers = ['licenceTransportManagers' => $details];
        $markers = $this->getTransportManagerMarkerService()
            ->generateMarkerTypes(['licenceTransportManagers'], ['licenceTransportManagers' => $transportManagers]);

        $placeholder->getContainer('tmMarkers')->set($markers);
    }

    /**
     * @param RouteParam $e
     */
    public function onApplicationTransportManagerMarker(RouteParam $e)
    {
        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $details = $this->getTransportManagerApplicationService()->getTmForApplication($e->getValue())['Results'];
        $transportManagers = ['applicationTransportManagers' => $details];
        $markers = $this->getTransportManagerMarkerService()
            ->generateMarkerTypes(
                ['applicationTransportManagers'],
                ['applicationTransportManagers' => $transportManagers]
            );

        $placeholder->getContainer('tmMarkers')->set($markers);
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

        $this->setTransportManagerService(
            $serviceLocator->get('Entity\TransportManager')
        );

        $this->setTransportManagerLicenceService(
            $serviceLocator->get('Entity\TransportManagerLicence')
        );

        $this->setTransportManagerApplicationService(
            $serviceLocator->get('Entity\TransportManagerApplication')
        );

        $transportManagerMarkerService = $serviceLocator
            ->get('Olcs\Service\Marker\MarkerPluginManager')
            ->get('Olcs\Service\Marker\TransportManagerMarkers');

        $this->setTransportManagerMarkerService($transportManagerMarkerService);
        return $this;
    }
}
