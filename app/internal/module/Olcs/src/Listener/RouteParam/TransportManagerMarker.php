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
 * Class TransportManagerMarker
 * @package Olcs\Listener\RouteParam
 */
class TransportManagerMarker implements ListenerAggregateInterface, FactoryInterface
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
        $this->getMarkerService()->addData(
            'transportManager',
            $this->getTransportManager($e->getValue())
        );
        $this->getMarkerService()->addData(
            'transportManagerApplications',
            $this->getTransportManagerApplicationData(null, $e->getValue())
        );
        $this->getMarkerService()->addData(
            'transportManagerLicences',
            $this->getTransportManagerLicenceData(null, $e->getValue())
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onLicenceTransportManagerMarker(RouteParam $e)
    {
        $this->getMarkerService()->addData(
            'transportManagerLicences',
            $this->getTransportManagerLicenceData($e->getValue())
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onApplicationTransportManagerMarker(RouteParam $e)
    {
        $this->getMarkerService()->addData(
            'transportManagerApplications',
            $this->getTransportManagerApplicationData($e->getValue())
        );
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
}
