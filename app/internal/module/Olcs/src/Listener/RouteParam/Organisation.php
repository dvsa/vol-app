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

    private $sidebarNavigationService;

    /**
     * @var \Olcs\Service\Marker\MarkerService
     */
    protected $markerService;

    public function getMarkerService()
    {
        return $this->markerService;
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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'organisation', [$this, 'onOrganisation'], 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onOrganisation(RouteParam $e)
    {
        $organisation = $this->getOrganisation($e->getValue());

        $navigationPlugin = $this->getViewHelperManager()->get('Navigation')->__invoke('navigation');

        $isIrfo = $organisation['isIrfo'] == 'Y';
        if (!$isIrfo) {
            // hide IRFO navigation
            $navigationPlugin->findById('operator_irfo')->setVisible(false);
            $navigationPlugin->findById('operator_fees')->setVisible(false);
        }

        if ($organisation['isDisqualified']) {
            $sidebarNav = $this->getSidebarNavigationService();
            $sidebarNav->findById('operator-decisions-disqualify')->setVisible(false);
        }

        if ($organisation['isUnlicensed']) {
            // Removed licenced only items
            $navigationPlugin->findById('operator_business_details')->setVisible(false);
            $navigationPlugin->findById('operator_fees')->setVisible(false);
            $navigationPlugin->findById('operator_documents')->setVisible(false);
            $navigationPlugin->findById('operator_licences_applications')->setVisible(false);
        } else {
            // Removed unlicenced only items
            $navigationPlugin->findById('unlicensed_operator_business_details')->setVisible(false);
            $navigationPlugin->findById('unlicensed_operator_cases')->setVisible(false);
            $navigationPlugin->findById('unlicensed_operator_vehicles')->setVisible(false);
        }

        $this->getMarkerService()->addData('organisation', $organisation);
    }

    /**
     * Get the Organisation data
     *
     * @param int $id
     *
     * @return array Organisation date
     * @throws ResourceNotFoundException
     */
    private function getOrganisation($id)
    {
        $query = $this->getAnnotationBuilder()->createQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\People::create(['id' => $id])
        );

        $response = $this->getQueryService()->send($query);

        if (!$response->isOk()) {
            throw new \Common\Exception\ResourceNotFoundException("Organisation id [$id] not found");
        }

        return $response->getResult();
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setAnnotationBuilder($serviceLocator->get('TransferAnnotationBuilder'));
        $this->setQueryService($serviceLocator->get('QueryService'));
        $this->setSidebarNavigationService($serviceLocator->get('right-sidebar'));
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setMarkerService($serviceLocator->get(\Olcs\Service\Marker\MarkerService::class));

        return $this;
    }

    /**
     * @return \Zend\Navigation\Navigation
     */
    public function getSidebarNavigationService()
    {
        return $this->sidebarNavigationService;
    }

    /**
     * @param \Zend\Navigation\Navigation $sidebarNavigationService
     * @return $this
     */
    public function setSidebarNavigationService($sidebarNavigationService)
    {
        $this->sidebarNavigationService = $sidebarNavigationService;
        return $this;
    }

    public function getAnnotationBuilder()
    {
        return $this->annotationBuilder;
    }

    public function getQueryService()
    {
        return $this->queryService;
    }

    public function setAnnotationBuilder($annotationBuilder)
    {
        $this->annotationBuilder = $annotationBuilder;
    }

    public function setQueryService($queryService)
    {
        $this->queryService = $queryService;
    }
}
