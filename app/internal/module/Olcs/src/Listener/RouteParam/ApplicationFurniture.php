<?php

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQuery;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Laminas\View\Model\ViewModel;
use Psr\Container\ContainerInterface;

class ApplicationFurniture implements
    ListenerAggregateInterface,
    FactoryInterface,
    QuerySenderAwareInterface,
    CommandSenderAwareInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;
    use QuerySenderAwareTrait;
    use CommandSenderAwareTrait;

    private $router;

    /**
     * @param $router
     * @return void
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @return \Laminas\Router\RouteStackInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'application',
            [$this, 'onApplicationFurniture'],
            $priority
        );
    }

    public function onApplicationFurniture(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $id = $routeParam->getValue();

        // for performance reasons this query should be the same as used in other Application RouteListeners
        $response = $this->getQuerySender()->send(ApplicationQuery::create(['id' => $id]));

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Application id [$id] not found");
        }

        $data = $response->getResult();

        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $inactiveAppStatuses = [
            RefData::APPLICATION_STATUS_NOT_SUBMITTED,
            RefData::APPLICATION_STATUS_UNDER_CONSIDERATION,
            RefData::APPLICATION_STATUS_GRANTED,
            RefData::APPLICATION_STATUS_NOT_TAKEN_UP,
            RefData::APPLICATION_STATUS_WITHDRAWN,
            RefData::APPLICATION_STATUS_REFUSED
        ];

        if (!in_array($data['status']['id'], $inactiveAppStatuses) || $data['isVariation']) {
            $licenceUrl = $this->getRouter()->assemble(
                ['licence' => $data['licence']['id']],
                ['name' => 'lva-licence']
            );

            $placeholder->getContainer('pageTitle')->set(
                sprintf('<a class="govuk-link" href="%s">%s</a> / %s', $licenceUrl, $data['licence']['licNo'], $id)
            );
        } elseif ($data['licence']['licNo']) {
            $placeholder->getContainer('pageTitle')->set(sprintf('%s / %s', $data['licence']['licNo'], $id));
        } else {
            $placeholder->getContainer('pageTitle')->set(sprintf('%s', $id));
        }

        $placeholder->getContainer('pageSubtitle')->set($data['licence']['organisation']['name']);
        $placeholder->getContainer('status')->set($data['status']);
        $placeholder->getContainer('horizontalNavigationId')->set('application');
        $placeholder->getContainer('isMlh')->set($data['isMlh']);

        $right = new ViewModel();
        $right->setTemplate('sections/application/partials/right');

        $placeholder->getContainer('right')->set($right);
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        $this->setQuerySender($container->get('QuerySender'));
        $this->setRouter($container->get('Router'));
        $this->setCommandSender($container->get('CommandSender'));
        return $this;
    }
}
