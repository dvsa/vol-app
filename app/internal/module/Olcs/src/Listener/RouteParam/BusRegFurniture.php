<?php

namespace Olcs\Listener\RouteParam;

use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use \Dvsa\Olcs\Transfer\Query\Bus\BusReg as ItemDto;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;
use Zend\View\Model\ViewModel;

/**
 * Bus Reg Furniture
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusRegFurniture implements
    ListenerAggregateInterface,
    FactoryInterface,
    QuerySenderAwareInterface,
    CommandSenderAwareInterface
{
    use ListenerAggregateTrait,
        ViewHelperManagerAwareTrait,
        QuerySenderAwareTrait,
        CommandSenderAwareTrait;

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
            RouteParams::EVENT_PARAM . 'busRegId',
            [$this, 'onBusRegFurniture'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onBusRegFurniture(RouteParam $e)
    {
        $id = $e->getValue();
        $busReg = $this->getBusReg($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('status')->set($busReg['status']);
        $placeholder->getContainer('pageTitle')->set($this->getPageTitle($busReg));
        $placeholder->getContainer('pageSubtitle')->set($this->getSubTitle($busReg));
        $placeholder->getContainer('horizontalNavigationId')->set('licence_bus');

        $right = new ViewModel();
        $right->setTemplate('sections/bus/partials/right');

        $placeholder->getContainer('right')->set($right);
    }

    /**
     * Get the Bus Reg data
     *
     * @param int $id
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getBusReg($id)
    {
        // for performance reasons this query should be the same as used in other BusReg RouteListeners
        $response = $this->getQuerySender()->send(
            ItemDto::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Bus Reg id [$id] not found");
        }

        return $response->getResult();
    }

    private function getPageTitle($busReg)
    {
        $urlPlugin = $this->getViewHelperManager()->get('Url');
        $licUrl = $urlPlugin->__invoke('licence/bus', ['licence' => $busReg['licence']['id']], [], true);
        return '<a href="' . $licUrl . '">' . $busReg['licence']['licNo'] . '</a>' . '/' . $busReg['routeNo'];
    }

    private function getSubTitle($busReg)
    {
        return $busReg['licence']['organisation']['name'] . ', Variation ' . $busReg['variationNo'];
    }
}
