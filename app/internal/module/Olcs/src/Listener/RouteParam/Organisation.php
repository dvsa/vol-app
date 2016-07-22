<?php

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Navigation\AbstractContainer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Navigation;

/**
 * Class Organisation
 * @package Olcs\Listener\RouteParam
 */
class Organisation implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ServiceLocatorAwareTrait;

    /** @var  AbstractContainer */
    private $sidebarNavigationService;
    /** @var  AnnotationBuilder */
    private $annotationBuilder;
    /** @var  QueryService */
    private $queryService;
    /** @var \Olcs\Service\Marker\MarkerService */
    private $markerService;

    /** @var  Navigation */
    private $navigationPlugin;

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

        /** @var AbstractContainer $navigationPlugin */
        $navigationPlugin = $this->navigationPlugin->__invoke('navigation');

        if ($organisation['isIrfo'] !== 'Y') {
            // hide IRFO navigation
            $navigationPlugin->findById('operator_irfo')->setVisible(false);
            $navigationPlugin->findById('operator_fees')->setVisible(false);
        }

        if ($organisation['isDisqualified']) {
            $sidebarNav = $this->sidebarNavigationService;
            $sidebarNav->findById('operator-decisions-disqualify')->setVisible(false);
        }

        if ($organisation['isUnlicensed']) {
            // Removed licenced only items
            $navigationPlugin->findById('operator_business_details')->setVisible(false);
            $navigationPlugin->findById('operator_fees')->setVisible(false);
            $navigationPlugin->findById('operator_documents')->setVisible(false);
            $navigationPlugin->findById('operator_licences')->setVisible(false);
            $navigationPlugin->findById('operator_applications')->setVisible(false);
        } else {
            // Removed unlicenced only items
            $navigationPlugin->findById('unlicensed_operator_business_details')->setVisible(false);
            $navigationPlugin->findById('unlicensed_operator_cases')->setVisible(false);
            $navigationPlugin->findById('unlicensed_operator_vehicles')->setVisible(false);
        }

        $this->markerService->addData('organisation', $organisation);
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
        // for performance reasons this query should be the same as the one in OrganisationFurniture
        $query = $this->annotationBuilder->createQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\People::create(['id' => $id])
        );

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->queryService->send($query);

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Organisation id [$id] not found");
        }

        return $response->getResult();
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->annotationBuilder = $serviceLocator->get('TransferAnnotationBuilder');
        $this->queryService = $serviceLocator->get('QueryService');
        $this->sidebarNavigationService = $serviceLocator->get('right-sidebar');
        $this->markerService = $serviceLocator->get(\Olcs\Service\Marker\MarkerService::class);
        $this->navigationPlugin = $serviceLocator->get('ViewHelperManager')->get('Navigation');

        return $this;
    }
}
