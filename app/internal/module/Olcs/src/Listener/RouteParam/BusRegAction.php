<?php

namespace Olcs\Listener\RouteParam;

use Psr\Container\ContainerInterface;
use Common\Service\Cqrs\Query\QueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use \Dvsa\Olcs\Transfer\Query\Bus\BusRegDecision as ItemDto;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\RefData;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;

/**
 * Class BusRegAction
 *
 * @package Olcs\Listener\RouteParam
 */
class BusRegAction implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;

    /**
     * @var Navigation
     */
    protected $sidebarNavigationService;

    protected $annotationBuilder;

    protected $queryService;

    /**
     * Get Annotation Builder
     *
     * @return AnnotationBuilder
     */
    public function getAnnotationBuilder()
    {
        return $this->annotationBuilder;
    }

    /**
     * Get Query Service
     *
     * @return QueryService
     */
    public function getQueryService()
    {
        return $this->queryService;
    }

    /**
     * Set annotation builder
     *
     * @param AnnotationBuilder $annotationBuilder the new Annotation Builder
     *
     * @return void
     */
    public function setAnnotationBuilder($annotationBuilder)
    {
        $this->annotationBuilder = $annotationBuilder;
    }

    /**
     * Set query service
     *
     * @param QueryService $queryService the new query service
     *
     * @return void
     */
    public function setQueryService($queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * Get Sidebar Navigation
     *
     * @return Navigation
     */
    public function getSidebarNavigationService()
    {
        return $this->sidebarNavigationService;
    }

    /**
     * Set Sidebar Navigation
     *
     * @param Navigation $sidebarNavigationService the new navigation service
     *
     * @return $this
     */
    public function setSidebarNavigationService($sidebarNavigationService)
    {
        $this->sidebarNavigationService = $sidebarNavigationService;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'busRegId',
            [$this, 'onBusRegAction'],
            $priority
        );
    }

    /**
     * Modify buttons for bus registration page
     *
     * @return void
     */
    public function onBusRegAction(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $id = $routeParam->getValue();
        $busReg = $this->getBusReg($id);

        $sidebarNav = $this->getSidebarNavigationService();

        // quick actions
        $sidebarNav->findOneBy('id', 'bus-registration-quick-actions-create-cancellation')
            ->setVisible($busReg['canCreateCancellation']);
        $sidebarNav->findOneBy('id', 'bus-registration-quick-actions-create-variation')
            ->setVisible($busReg['canCreateVariation']);
        $sidebarNav->findOneBy('id', 'bus-registration-quick-actions-print-reg-letter')
            ->setVisible($busReg['canPrint']);
        $sidebarNav->findOneBy('id', 'bus-registration-quick-actions-request-new-route-map')
            ->setVisible($busReg['canRequestNewRouteMap']);
        $sidebarNav->findOneBy('id', 'bus-registration-quick-actions-request-withdrawn')
            ->setVisible($busReg['canWithdraw']);
        $sidebarNav->findOneBy('id', 'bus-registration-quick-actions-republish')
            ->setVisible($busReg['canRepublish']);

        #// decisions
        $sidebarNav->findOneBy('id', 'bus-registration-decisions-admin-cancel')
            ->setVisible($busReg['canCancelByAdmin']);
        $sidebarNav->findOneBy('id', 'bus-registration-decisions-grant')
            ->setVisible($busReg['isGrantable'])
            ->setClass(
                $this->shouldOpenGrantButtonInModal($busReg) ? 'govuk-button govuk-button--secondary js-modal-ajax' : 'govuk-button govuk-button--secondary'
            );
        $sidebarNav->findOneBy('id', 'bus-registration-decisions-refuse')
            ->setVisible($busReg['canRefuse']);
        $sidebarNav->findOneBy('id', 'bus-registration-decisions-refuse-by-short-notice')
            ->setVisible($busReg['canRefuseByShortNotice']);
        $sidebarNav->findOneBy('id', 'bus-registration-decisions-reset-registration')
            ->setVisible($busReg['canResetRegistration']);
    }

    /**
     * Get the Bus Reg data
     *
     * @param int $id Bus Registration identifier
     *
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getBusReg($id)
    {
        $query = $this->getAnnotationBuilder()->createQuery(
            ItemDto::create(['id' => $id])
        );

        $response = $this->getQueryService()->send($query);

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Bus Reg id [$id] not found");
        }

        return $response->getResult();
    }

    /**
     * Determine whether the grant button should open a modal
     *
     * @param array $busReg the bus reg data from the backend
     *
     * @return bool
     */
    private function shouldOpenGrantButtonInModal($busReg)
    {
        return ($busReg['status']['id'] === RefData::BUSREG_STATUS_VARIATION);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BusRegAction
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : BusRegAction
    {
        $this->setAnnotationBuilder($container->get('TransferAnnotationBuilder'));
        $this->setQueryService($container->get('QueryService'));
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        $this->setSidebarNavigationService($container->get('right-sidebar'));
        return $this;
    }
}
