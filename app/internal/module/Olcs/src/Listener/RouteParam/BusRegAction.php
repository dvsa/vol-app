<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use \Dvsa\Olcs\Transfer\Query\Bus\BusRegDecision as ItemDto;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
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
     * @var \Zend\Navigation\Navigation
     */
    protected $sidebarNavigationService;

    protected $annotationBuilder;

    protected $queryService;

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
    public function getSidebarNavigationService()
    {
        return $this->sidebarNavigationService;
    }

    /**
     * @param \Zend\Navigation\Navigation $sidebarNavigationService
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
     * @param ServiceLocatorInterface $serviceLocator
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
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'busRegId', [$this, 'onBusRegAction'], 1);
    }

    /**
     * @param RouteParam $e
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
            ->setVisible($this->shouldShowCreateVariationButton($busReg));
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

    private function shouldShowCreateVariationButton($busReg)
    {
        return ($busReg['isLatestVariation'] && ($busReg['status']['id'] === RefData::BUSREG_STATUS_REGISTERED));
    }

    private function shouldOpenGrantButtonInModal($busReg)
    {
        return ($busReg['status']['id'] === RefData::BUSREG_STATUS_VARIATION);
    }
}
