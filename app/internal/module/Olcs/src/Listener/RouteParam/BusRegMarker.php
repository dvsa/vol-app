<?php

namespace Olcs\Listener\RouteParam;

use Psr\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;

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

    public function onBusRegMarker(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $busReg = $this->getBusRegData($routeParam->getValue());
        $this->getMarkerService()->addData('busReg', $busReg);
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

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BusRegMarker
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : BusRegMarker
    {
        $this->setMarkerService($container->get(\Olcs\Service\Marker\MarkerService::class));
        $this->setAnnotationBuilderService($container->get('TransferAnnotationBuilder'));
        $this->setQueryService($container->get('QueryService'));
        return $this;
    }
}
