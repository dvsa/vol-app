<?php

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Dvsa\Olcs\Transfer\Command\Audit\ReadTransportManager;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Zend\View\Model\ViewModel;
use Common\RefData;

/**
 * Transport Manager Furniture
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerFurniture implements
    ListenerAggregateInterface,
    FactoryInterface,
    QuerySenderAwareInterface,
    CommandSenderAwareInterface
{
    use ListenerAggregateTrait,
        ViewHelperManagerAwareTrait,
        QuerySenderAwareTrait,
        CommandSenderAwareTrait;

    protected $tmStatuses = [
        RefData::TRANSPORT_MANAGER_STATUS_CURRENT => 'green',
        RefData::TRANSPORT_MANAGER_STATUS_DISQUALIFIED => 'red',
        RefData::TRANSPORT_MANAGER_STATUS_REMOVED => 'grey'
    ];

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
            RouteParams::EVENT_PARAM . 'transportManager',
            [$this, 'onTransportManager'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onTransportManager(RouteParam $e)
    {
        $id = $e->getValue();

        $this->getCommandSender()->send(ReadTransportManager::create(['id' => $id]));

        $data = $this->getTransportManager($id);

        $url = $this->getViewHelperManager()
            ->get('url')
            ->__invoke('transport-manager/details', ['transportManager' => $data['id']], [], true);

        $pageTitle = sprintf(
            '<a href="%s">%s %s</a><span class="status %s">%s</span>',
            $url,
            $data['homeCd']['person']['forename'],
            $data['homeCd']['person']['familyName'],
            $this->tmStatuses[$data['tmStatus']['id']],
            $data['tmStatus']['description']
        );

        $this->getViewHelperManager()->get('placeholder')->getContainer('pageTitle')->set($pageTitle);

        $right = new ViewModel();
        $right->setTemplate('sections/transport-manager/partials/right');
        $this->getViewHelperManager()->get('placeholder')->getContainer('right')->set($right);
        $this->getViewHelperManager()->get('placeholder')->getContainer('horizontalNavigationId')
            ->set('transport_manager');
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
        $response = $this->getQuerySender()->send(
            \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Error cannot get Transport Manager id [$id]");
        }

        return $response->getResult();
    }
}
