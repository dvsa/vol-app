<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BusRegMarker
 * @package Olcs\Listener\RouteParam
 */
class BusRegMarker implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    private $annotationBuilderService;
    private $queryService;

    /**
     * @var \Olcs\Service\Marker\MarkerService
     */
    protected $markerService;

    public function getAnnotationBuilderService()
    {
        return $this->annotationBuilderService;
    }

    public function getQueryService()
    {
        return $this->queryService;
    }

    public function getMarkerService()
    {
        return $this->markerService;
    }

    public function setAnnotationBuilderService($annotationBuilderService)
    {
        $this->annotationBuilderService = $annotationBuilderService;
        return $this;
    }

    public function setQueryService($queryService)
    {
        $this->queryService = $queryService;
        return $this;
    }

    public function setMarkerService(\Olcs\Service\Marker\MarkerService $markerService)
    {
        $this->markerService = $markerService;
        return $this;
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
        $busReg = $this->getBusRegData($e->getValue());
        $this->getMarkerService()->addData('busReg', $busReg);
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setMarkerService($serviceLocator->get(\Olcs\Service\Marker\MarkerService::class));
        $this->setAnnotationBuilderService($serviceLocator->get('TransferAnnotationBuilder'));
        $this->setQueryService($serviceLocator->get('QueryService'));

        return $this;
    }

    /**
     * Get BusReg Data
     *
     * @param int $busRegId
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getBusRegData($busRegId)
    {
        // for performance reasons this query should be the same as used in other BusReg RouteListeners
        $query = $this->getAnnotationBuilderService()->createQuery(
            \Dvsa\Olcs\Transfer\Query\Bus\BusReg::create(['id' => $busRegId])
        );

        $response = $this->getQueryService()->send($query);
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting BusReg data');
        }

        return $response->getResult();
    }
}
