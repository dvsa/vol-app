<?php

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQuery;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Zend\View\Model\ViewModel;

/**
 * Variation Furniture
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
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
     * @return \Zend\Mvc\Router\RouteStackInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        $this->setQuerySender($serviceLocator->get('QuerySender'));
        $this->setRouter($serviceLocator->get('Router'));

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
            RouteParams::EVENT_PARAM . 'application',
            [$this, 'onVariationFurniture'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onVariationFurniture(RouteParam $e)
    {
        $id = $e->getValue();

        $response = $this->getQuerySender()->send(ApplicationQuery::create(['id' => $id]));

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Variation id [$id] not found");
        }

        $data = $response->getResult();

        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $html = '<a href="%1$s">%2$s</a> / %3$s';
        $licenceUrl = $this->getRouter()->assemble(['licence' => $data['licence']['id']], ['name' => 'lva-licence']);
        $placeholder->getContainer('pageTitle')->set(sprintf($html, $licenceUrl, $data['licence']['licNo'], $id));
        $placeholder->getContainer('pageSubtitle')->set($data['licence']['organisation']['name']);
        $placeholder->getContainer('status')->set($data['status']['id']);
        $placeholder->getContainer('horizontalNavigationId')->set('application');

        $right = new ViewModel();
        $right->setTemplate('sections/variation/partials/right');

        $placeholder->getContainer('right')->set($right);
    }
}
