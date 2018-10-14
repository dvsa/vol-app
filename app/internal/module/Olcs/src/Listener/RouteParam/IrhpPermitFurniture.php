<?php

namespace Olcs\Listener\RouteParam;

use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Dvsa\Olcs\Transfer\Query\Permits\ById as ItemDto;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;
use Zend\View\Model\ViewModel;

/**
 * IRHP Permit Furniture
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class IrhpPermitFurniture implements
    ListenerAggregateInterface,
    FactoryInterface,
    QuerySenderAwareInterface,
    CommandSenderAwareInterface
{
    use ListenerAggregateTrait,
        ViewHelperManagerAwareTrait,
        QuerySenderAwareTrait,
        CommandSenderAwareTrait;

    protected $sidebarNavigationService;

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
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setQuerySender($serviceLocator->get('QuerySender'));
        $this->setCommandSender($serviceLocator->get('CommandSender'));
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setSidebarNavigationService($serviceLocator->get('right-sidebar'));

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
            RouteParams::EVENT_PARAM . 'permitid',
            [$this, 'onIrhpPermitFurniture'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onIrhpPermitFurniture(RouteParam $e)
    {
        $id = $e->getValue();
        $irhpPermit = $this->getIrhpPermit($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $placeholder->getContainer('irhpPermit')->set($irhpPermit);
        $placeholder->getContainer('status')->set($irhpPermit['status']);
        $placeholder->getContainer('pageTitle')->set($this->getPageTitle($irhpPermit));
        $placeholder->getContainer('pageSubtitle')->set($this->getSubTitle($irhpPermit));
        $placeholder->getContainer('horizontalNavigationId')->set('licence_irhp_permits');

        $sidebarNav = $this->getSidebarNavigationService();

        // quick actions
        $sidebarNav->findOneBy('id', 'irhp-permit-quick-actions-cancel')
            ->setVisible($irhpPermit['canBeCancelled']);


        // decisions
        $sidebarNav->findOneBy('id', 'irhp-permit-decisions-submit')
            ->setVisible($irhpPermit['canBeSubmitted']);


        $sidebarNav->findOneBy('id', 'irhp-permit-decisions-withdraw')
            ->setVisible($irhpPermit['canBeWithdrawn']);

        $sidebarNav->findOneBy('id', 'irhp-permit-decisions-accept')
            ->setVisible($irhpPermit['isAwaitingFee']);

        $sidebarNav->findOneBy('id', 'irhp-permit-decisions-decline')
            ->setVisible($irhpPermit['canBeDeclined']);



        $right = new ViewModel();
        $right->setTemplate('sections/irhp-permit/partials/right');

        $placeholder->getContainer('right')->set($right);
    }

    /**
     * Get the Irhp Permit data
     *
     * @param int $id
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getIrhpPermit($id)
    {
        $response = $this->getQuerySender()->send(
            ItemDto::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Irhp Permit id [$id] not found");
        }

        return $response->getResult();
    }

    private function getPageTitle($irhpPermit)
    {
        $urlPlugin = $this->getViewHelperManager()->get('Url');
        $licUrl = $urlPlugin->__invoke('licence/permits', ['licence' => $irhpPermit['licence']['id']], [], false);
        return '<a href="' . $licUrl . '">' . $irhpPermit['licence']['licNo'] . '</a>' . '/' . $irhpPermit['id'];
    }

    private function getSubTitle($irhpPermit)
    {
        return $irhpPermit['licence']['organisation']['name'] . ', Permit Application';
    }
}
