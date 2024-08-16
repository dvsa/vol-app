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

class OrganisationFurniture implements
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
            RouteParams::EVENT_PARAM . 'organisation',
            [$this, 'onOrganisation'],
            $priority
        );
    }

    public function onOrganisation(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $id = $routeParam->getValue();

        $organisation = $this->getOrganisation($id);
        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $placeholder->getContainer('horizontalNavigationId')->set('operator');
        $placeholder->getContainer('organisationIsMlh')->set($organisation['organisationIsMlh']);
        $placeholder->getContainer('isMlh')->set('');

        $right = new ViewModel();
        $right->setTemplate('sections/operator/partials/right');
        $placeholder->getContainer('right')->set($right);

        if ($organisation['isUnlicensed']) {
            $placeholder->getContainer('pageSubtitle')->set($organisation['licence']['licNo']);
            $right->setVariable('hideQuickActions', true);
        }

        $pageTitle = !empty($organisation['name']) ? $organisation['name'] : '';

        $placeholder->getContainer('pageTitle')->set($pageTitle);
    }

    /**
     * Get the Organisation data
     *
     * @param int $id
     *
     * @return array Organisation date
     * @throws ResourceNotFoundException
     */
    private function getOrganisation($id)
    {
        // for performance reasons this query should be the same as the one in OrganisationFurniture
        $response = $this->getQuerySender()->send(
            \Dvsa\Olcs\Transfer\Query\Organisation\People::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Organisation id [$id] not found");
        }

        return $response->getResult();
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return OrganisationFurniture
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OrganisationFurniture
    {
        $this->setQuerySender($container->get('QuerySender'));
        $this->setCommandSender($container->get('CommandSender'));
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        return $this;
    }
}
