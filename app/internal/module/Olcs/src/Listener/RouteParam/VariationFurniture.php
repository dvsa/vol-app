<?php

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQuery;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class VariationFurniture implements ListenerAggregateInterface, FactoryInterface, QuerySenderAwareInterface
{
    use ListenerAggregateTrait,
        ViewHelperManagerAwareTrait,
        QuerySenderAwareTrait;

    private $router;

    /**
     * @param $router
     * @return $this
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
            [$this, 'onVariationFurniture'],
            $priority
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onVariationFurniture(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $id = $routeParam->getValue();

        $response = $this->getQuerySender()->send(ApplicationQuery::create(['id' => $id]));

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Variation id [$id] not found");
        }

        $data = $response->getResult();

        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $html = '<a class="govuk-link" href="%1$s">%2$s</a> / %3$s';
        $licenceUrl = $this->getRouter()->assemble(['licence' => $data['licence']['id']], ['name' => 'lva-licence']);
        $placeholder->getContainer('pageTitle')->set(sprintf($html, $licenceUrl, $data['licence']['licNo'], $id));
        $placeholder->getContainer('pageSubtitle')->set($data['licence']['organisation']['name']);
        $placeholder->getContainer('status')->set($data['status']);
        $placeholder->getContainer('horizontalNavigationId')->set('application');

        $right = new ViewModel();
        $right->setTemplate('sections/variation/partials/right');

        $placeholder->getContainer('right')->set($right);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return VariationFurniture
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : VariationFurniture
    {
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        $this->setQuerySender($container->get('QuerySender'));
        $this->setRouter($container->get('Router'));
        return $this;
    }
}
