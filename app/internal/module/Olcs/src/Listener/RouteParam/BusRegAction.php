<?php

namespace Olcs\Listener\RouteParam;

use Common\Service\Cqrs\Query\QueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use \Dvsa\Olcs\Transfer\Query\Bus\BusRegDecision as ItemDto;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
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
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator the service locator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setAnnotationBuilder($serviceLocator->get('TransferAnnotationBuilder'));
        $this->setQueryService($serviceLocator->get('QueryService'));
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setSidebarNavigationService($serviceLocator->get('right-sidebar'));

        return $this;
    }

    /**
     * Attach one or more listeners
     *
     * Implementers may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events the event manager
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'busRegId', [$this, 'onBusRegAction'], 1);
    }

    /**
     * Modify buttons for bus registration page
     *
     * @param RouteParam $e The RouteParam event
     *
     * @return void
     */
    public function onBusRegAction(RouteParam $e)
    {
        $id = $e->getValue();
        $busReg = $this->getBusReg($id);

        $sidebarNav = $this->getSidebarNavigationService();

        // quick actions
        $sidebarNav->findOneBy('id', 'bus-registration-quick-actions-create-cancellation')
            ->setVisible($busReg['canCreateCancellation']);
        $sidebarNav->findOneBy('id', 'bus-registration-quick-actions-create-variation')
            ->setVisible($busReg['canCreateVariation']);
        $sidebarNav->findOneBy('id', 'bus-registration-quick-actions-print-reg-letter')
            ->setVisible($busReg['canPrintLetter']);
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
                $this->shouldOpenGrantButtonInModal($busReg) ? 'action--secondary js-modal-ajax' : 'action--secondary'
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
}
