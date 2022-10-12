<?php
/**
 * This listener is for the Case Markers
 */

namespace Olcs\Listener\RouteParam;

use Interop\Container\ContainerInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class CaseMarker
 * @package Olcs\Listener\RouteParam
 */
class CaseMarker implements ListenerAggregateInterface, FactoryInterface
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
            RouteParams::EVENT_PARAM . 'case',
            array($this, 'onCase'),
            $priority
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onCase(RouteParam $e)
    {
        $case = $this->getCaseData($e->getValue());
        $this->getMarkerService()->addData('organisation', $case['licence']['organisation']);
        $this->getMarkerService()->addData('cases', [$case]);
        $this->getMarkerService()->addData('configCase', ['hideLink' => true]);
    }

    /**
     * Get Case data
     *
     * @param int $caseId
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getCaseData($caseId)
    {
        // for performance reasons this query should be the same as used in other Case RouteListeners
        $query = $this->getAnnotationBuilderService()->createQuery(
            \Dvsa\Olcs\Transfer\Query\Cases\Cases::create(['id' => $caseId])
        );

        $response = $this->getQueryService()->send($query);
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting Case data');
        }

        return $response->getResult();
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : CaseMarker
    {
        return $this->__invoke($serviceLocator, CaseMarker::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CaseMarker
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : CaseMarker
    {
        $this->setMarkerService($container->get(\Olcs\Service\Marker\MarkerService::class));
        $this->setAnnotationBuilderService($container->get('TransferAnnotationBuilder'));
        $this->setQueryService($container->get('QueryService'));
        return $this;
    }
}
