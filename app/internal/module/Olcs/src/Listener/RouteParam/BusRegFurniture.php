<?php

namespace Olcs\Listener\RouteParam;

use Psr\Container\ContainerInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg as ItemDto;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;
use Laminas\View\Model\ViewModel;

class BusRegFurniture implements
    ListenerAggregateInterface,
    FactoryInterface,
    QuerySenderAwareInterface,
    CommandSenderAwareInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;
    use QuerySenderAwareTrait;
    use CommandSenderAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'busRegId',
            [$this, 'onBusRegFurniture'],
            $priority
        );
    }

    public function onBusRegFurniture(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $id = $routeParam->getValue();
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
        return '<a class="govuk-link" href="' . $licUrl . '">' . $busReg['licence']['licNo'] . '</a>' . '/' . $busReg['routeNo'];
    }

    private function getSubTitle($busReg)
    {
        return $busReg['licence']['organisation']['name'] . ', Variation ' . $busReg['variationNo'];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BusRegFurniture
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusRegFurniture
    {
        $this->setQuerySender($container->get('QuerySender'));
        $this->setCommandSender($container->get('CommandSender'));
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        return $this;
    }
}
