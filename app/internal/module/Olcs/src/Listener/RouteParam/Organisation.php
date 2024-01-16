<?php

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Navigation\AbstractContainer;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\Navigation;

class Organisation implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

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
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'organisation',
            [$this, 'onOrganisation'],
            $priority
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onOrganisation(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $organisation = $this->getOrganisation($routeParam->getValue());

        /** @var AbstractContainer $navigationPlugin */
        $navigationPlugin = $this->navigationPlugin->__invoke('navigation');

        if ($organisation['isIrfo'] !== 'Y') {
            // hide IRFO navigation
            $navigationPlugin->findById('operator_irfo')->setVisible(false);
            $navigationPlugin->findById('operator_fees')->setVisible(false);
        }

        if ($organisation['isDisqualified'] || $organisation['isUnlicensed']) {
            $this->sidebarNavigationService->findById('operator-decisions-disqualify')->setVisible(false);
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
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Organisation
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Organisation
    {
        $this->annotationBuilder = $container->get('TransferAnnotationBuilder');
        $this->queryService = $container->get('QueryService');
        $this->sidebarNavigationService = $container->get('right-sidebar');
        $this->markerService = $container->get(\Olcs\Service\Marker\MarkerService::class);
        $this->navigationPlugin = $container->get('ViewHelperManager')->get('Navigation');
        return $this;
    }
}
