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

class TransportManagerMarker implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    private $annotationBuilderService;
    private $queryService;
    private $applicationService;

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

    public function getApplicationService()
    {
        return $this->applicationService;
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

    public function setApplicationService($applicationService)
    {
        $this->applicationService = $applicationService;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'transportManager',
            [$this, 'onTransportManagerMarker'],
            $priority
        );
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'licence',
            [$this, 'onLicenceTransportManagerMarker'],
            $priority
        );
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'application',
            [$this, 'onApplicationTransportManagerMarker'],
            $priority
        );
    }

    public function onTransportManagerMarker(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $this->getMarkerService()->addData(
            'transportManager',
            $this->getTransportManager($routeParam->getValue())
        );
        $this->getMarkerService()->addData(
            'transportManagerApplications',
            $this->getTransportManagerApplicationData(null, $routeParam->getValue())
        );
        $this->getMarkerService()->addData(
            'transportManagerLicences',
            $this->getTransportManagerLicenceData(null, $routeParam->getValue())
        );
        $this->getMarkerService()->addData('page', 'transportManager');
    }

    public function onLicenceTransportManagerMarker(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $this->getMarkerService()->addData(
            'transportManagerLicences',
            $this->getTransportManagerLicenceData($routeParam->getValue())
        );
        $this->getMarkerService()->addData('page', 'transportManagerLicence');
    }

    /**
     * @param RouteParam $e
     */
    public function onApplicationTransportManagerMarker(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $this->getMarkerService()->addData(
            'transportManagerApplications',
            $this->getTransportManagerApplicationData($routeParam->getValue())
        );

        $routeName = $this->getApplicationService()->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
        if (str_starts_with($routeName, 'lva-variation')) {
            $this->addTransportManagerFromLicenceData($routeParam->getValue());
            $this->getMarkerService()->addData('page', 'transportManagerVariation');
        } else {
            $this->getMarkerService()->addData('page', 'transportManagerApplication');
        }
    }

    public function getTransportManager($tmId)
    {
        $query = $this->getAnnotationBuilderService()->createQuery(
            \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(
                [
                    'id' => $tmId
                ]
            )
        );

        $response = $this->getQueryService()->send($query);
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting TransportManager data');
        }

        return $response->getResult();
    }

    /**
     * Get Transport Manager Application data fot either an applciation or a transport manager
     *
     * @param int $applicationId
     * @param int $tmId
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getTransportManagerApplicationData($applicationId, $tmId = null)
    {
        $query = $this->getAnnotationBuilderService()->createQuery(
            \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(
                ['application' => $applicationId, 'transportManager' => $tmId]
            )
        );

        $response = $this->getQueryService()->send($query);
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting TransportManagerApplication data');
        }

        return $response->getResult()['results'];
    }

    /**
     * Get Transport Manager Licence data fot either a licence or a transport manager
     *
     * @param int $licenceId
     * @param int $tmId
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getTransportManagerLicenceData($licenceId, $tmId = null)
    {
        $query = $this->getAnnotationBuilderService()->createQuery(
            \Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetList::create(
                ['licence' => $licenceId, 'transportManager' => $tmId]
            )
        );

        $response = $this->getQueryService()->send($query);
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting TransportManagerLicence data');
        }

        return $response->getResult()['results'];
    }

    /**
     * Get Transport Manager Licence data based on variation id
     *
     * @param int $variationId Variation ID
     *
     * @return void
     * @throws \RuntimeException
     */
    protected function addTransportManagerFromLicenceData($variationId)
    {
        $query = $this->getAnnotationBuilderService()->createQuery(
            \Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetListByVariation::create(
                ['variation' => $variationId]
            )
        );

        $response = $this->getQueryService()->send($query);
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting TransportManagerLicence data using variation id ');
        }
        $result = $response->getResult();

        // Only add "transportManagersFromLicence" data if the variation requires TM's to have an SI qualification
        if (isset($result['extra']['requiresSiQualification']) && $result['extra']['requiresSiQualification'] == true) {
            $this->getMarkerService()->addData('transportManagersFromLicence', $result['results']);
        }
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TransportManagerMarker
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagerMarker
    {
        $this->setMarkerService($container->get(\Olcs\Service\Marker\MarkerService::class));
        $this->setAnnotationBuilderService($container->get('TransferAnnotationBuilder'));
        $this->setQueryService($container->get('QueryService'));
        $this->setApplicationService($container->get('Application'));
        return $this;
    }
}
