<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'busRegId',
            [$this, 'onBusRegMarker'],
            $priority
        );
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
