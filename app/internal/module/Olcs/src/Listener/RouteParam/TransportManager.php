<?php

namespace Olcs\Listener\RouteParam;

use Psr\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Olcs\Controller\TransportManager\Details\TransportManagerDetailsResponsibilityController;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Dvsa\Olcs\Transfer\Query\Nr\ReputeUrl as ReputeUrlQry;
use Common\RefData;

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
     * @var \Laminas\Navigation\Navigation
     */
    protected $sidebarNavigation;

    /**
     * @var \LmcRbacMvc\Service\AuthorizationService
     */
    protected $authService;

    /**
     * @return \LmcRbacMvc\Service\AuthorizationService
     */
    public function getAuthService()
    {
        return $this->authService;
    }

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
     * @return \Laminas\Navigation\Navigation
     */
    public function getSidebarNavigation()
    {
        return $this->sidebarNavigation;
    }

    /**
     * @param \Laminas\Navigation\Navigation $sidebarNavigation
     */
    public function setSidebarNavigation($sidebarNavigation)
    {
        $this->sidebarNavigation = $sidebarNavigation;
    }

    /**
     * Set auth service
     *
     * @param \LmcRbacMvc\Service\AuthorizationService $authorisationService
     */
    public function setAuthService($authService)
    {
        $this->authService = $authService;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'transportManager',
            [$this, 'onTransportManager'],
            $priority
        );
    }

    public function onTransportManager(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $id = $routeParam->getValue();
        $context = $routeParam->getContext();
        $data = $this->getTransportManager($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('transportManager')->set($data);

        $latestNote = $data['latestNote']['comment'] ?? '';
        $placeholder->getContainer('note')->set($latestNote);

        //only show print form link for one controller and action
        if ($context['controller'] == TransportManagerDetailsResponsibilityController::class
             && $context['action'] == 'edit-tm-application'
        ) {
             $this->getSidebarNavigation()
                 ->findById('transport_manager_details_review')
                 ->setVisible(true);
        }

        $reputeUrl = $this->getReputeUrl($id);

        if ($reputeUrl !== null && $this->getAuthService()->isGranted(RefData::PERMISSION_INTERNAL_EDIT)) {
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
     * Get the TransportManager data
     *
     * @param int   $id Transport Manager ID
     *
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getTransportManager($id)
    {
        // for performance reasons this query should be the same as used in other TM RouteListeners
        $query = $this->getAnnotationBuilder()->createQuery(
            \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(['id' => $id])
        );

        $response = $this->getQueryService()->send($query);

        if (!$response->isOk()) {
            throw new \RuntimeException("Error cannot get Transport Manager id [$id]");
        }

        return $response->getResult();
    }

    /**
     * Get the TM repute url
     *
     * @param int $id Transport Manager ID
     *
     * @return array
     * @throws \RuntimeException
     */
    private function getReputeUrl($id)
    {
        $query = $this->getAnnotationBuilder()->createQuery(ReputeUrlQry::create(['id' => $id]));

        $response = $this->getQueryService()->send($query);

        //sometimes there will genuinely be no repute url,
        //in these cases $response->isOk() will still return true
        if (!$response->isOk()) {
            throw new \RuntimeException("Error cannot get repute url");
        }

        return $response->getResult()['reputeUrl'];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->setAnnotationBuilder($container->get('TransferAnnotationBuilder'));
        $this->setQueryService($container->get('QueryService'));
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        $this->setSidebarNavigation($container->get('right-sidebar'));
        $this->setAuthService($container->get(\LmcRbacMvc\Service\AuthorizationService::class));
        return $this;
    }
}
