<?php

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Psr\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Laminas\View\Model\ViewModel;

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
        $data = $this->getTransportManager($id);

        $url = $this->getViewHelperManager()
            ->get('url')
            ->__invoke('transport-manager/details', ['transportManager' => $data['id']], [], true);

        $pageTitle = sprintf(
            '<a class="govuk-link" href="%s">%s %s</a>',
            $url,
            $data['homeCd']['person']['forename'],
            $data['homeCd']['person']['familyName']
        );
        $this->getViewHelperManager()->get('placeholder')->getContainer('status')->set($data['tmStatus']);
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
        // for performance reasons this query should be the same as used in other TM RouteListeners
        $response = $this->getQuerySender()->send(
            \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Error cannot get Transport Manager id [$id]");
        }

        return $response->getResult();
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TransportManagerFurniture
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : TransportManagerFurniture
    {
        $this->setQuerySender($container->get('QuerySender'));
        $this->setCommandSender($container->get('CommandSender'));
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        return $this;
    }
}
