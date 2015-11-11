<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Olcs\Service\Nr\RestHelper as NrRestHelper;
use Common\RefData;

/**
 * Class Cases
 * @package Olcs\Listener\RouteParam
 */
class TransportManager implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;

    /**
     * @var \Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder
     */
    protected $annotationBuilder;

    /**
     * @var \Common\Service\Cqrs\Query\QueryService
     */
    protected $queryService;

    /**
     * @var \Olcs\Service\Nr\RestHelper
     */
    protected $nrService;

    /**
     * @var \Zend\Navigation\Navigation
     */
    protected $sidebarNavigation;

    /**
     * @return \Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder
     */
    public function getAnnotationBuilder()
    {
        return $this->annotationBuilder;
    }

    /**
     * @return \Common\Service\Cqrs\Query\QueryService
     */
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

    /**
     * @return \Zend\Navigation\Navigation
     */
    public function getSidebarNavigation()
    {
        return $this->sidebarNavigation;
    }

    /**
     * @param \Zend\Navigation\Navigation $sidebarNavigation
     */
    public function setSidebarNavigation($sidebarNavigation)
    {
        $this->sidebarNavigation = $sidebarNavigation;
    }

    /**
     * @return \Olcs\Service\Nr\RestHelper
     */
    public function getNrService()
    {
        return $this->nrService;
    }

    /**
     * @param mixed $nrService
     */
    public function setNrService($nrService)
    {
        $this->nrService = $nrService;
    }

    /**
     * Attach one or more listeners
     *
     * Implementers may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'transportManager', [$this, 'onTransportManager'], 1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onTransportManager(RouteParam $e)
    {
        $id = $e->getValue();
        $context = $e->getContext();
        $data = $this->getTransportManager($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('transportManager')->set($data);

        //only show print form link for one controller and action
        if ($context['controller'] == 'TMDetailsResponsibilityController'
             && $context['action'] == 'edit-tm-application'
        ) {
             $this->getSidebarNavigation()
                 ->findById('transport_manager_details_review')
                 ->setVisible(true);
        }

        /* @to-do temporarily removed this until it can be enabled properly on dev */
        //$reputeUrl = $this->getNrService()->fetchTmReputeUrl($id);
        $reputeUrl = null;

        if ($reputeUrl !== null) {
            $this->getSidebarNavigation()
                 ->findById('transport-manager-quick-actions-check-repute')
                 ->setVisible(true)
                 ->setUri($reputeUrl);
        }

        if (!is_null($data['removedDate'])) {
            $this->getSidebarNavigation()
                ->findById('transport-manager-quick-actions-remove')
                ->setVisible(false);
        }
        if ($data['tmStatus']['id'] !== RefData::TRANSPORT_MANAGER_STATUS_DISQUALIFIED) {
            $this->getSidebarNavigation()
                ->findById('transport-manager-quick-actions-undo-disqualification')
                ->setVisible(false);
        }

        $this->hideShowMergeButtons($data);
    }

    /**
     * Toggle visibility of the TM merge buttins
     *
     * @param array $tmData
     */
    private function hideShowMergeButtons($tmData)
    {
        // if hasn't been merged then hide the unmerge button
        if (!$tmData['hasBeenMerged']) {
            $this->getSidebarNavigation()
                ->findById('transport-manager-quick-actions-unmerge')
                ->setVisible(false);
        }

        if (!empty($tmData['removedDate']) || $tmData['hasBeenMerged']) {
            $this->getSidebarNavigation()
                ->findById('transport-manager-quick-actions-merge')
                ->setVisible(false);
        }
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
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setSidebarNavigation($serviceLocator->get('right-sidebar'));
        $this->setNrService($serviceLocator->get(NrRestHelper::class));

        return $this;
    }

    /**
     * Get the TransportManager data
     *
     * @param int   $id Transport Manager ID
     *
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getTransportManager($id)
    {
        $query = $this->getAnnotationBuilder()->createQuery(
            \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(['id' => $id])
        );

        $response = $this->getQueryService()->send($query);

        if (!$response->isOk()) {
            throw new \RuntimeException("Error cannot get Transport Manager id [$id]");
        }

        return $response->getResult();
    }
}
