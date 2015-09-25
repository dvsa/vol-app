<?php

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Dvsa\Olcs\Transfer\Query\Application\Application;
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
 * Application Furniture
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationFurniture implements ListenerAggregateInterface, FactoryInterface, QuerySenderAwareInterface
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
            [$this, 'onApplicationFurniture'],
            1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onApplicationFurniture(RouteParam $e)
    {
        $id = $e->getValue();

        $response = $this->getQuerySender()->send(Application::create(['id' => $id]));

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

        if (!in_array($data['status']['id'], $inactiveAppStatuses)) {
            $html = '<a href="%s">%s</a> / %s';
            $licenceUrl = $this->getRouter()->assemble(['licence' => $data['licence']['id']], ['name' => 'lva-licence']);
            $placeholder->getContainer('pageTitle')->set(sprintf($html, $licenceUrl, $data['licence']['licNo'], $id));
        } elseif ($data['licence']['licNo']) {
            $html = '%s / %s';
            $placeholder->getContainer('pageTitle')->set(sprintf($html, $data['licence']['licNo'], $id));
        } else {
            $html = '%s';
            $placeholder->getContainer('pageTitle')->set(sprintf($html, $id));
        }

        $placeholder->getContainer('pageSubtitle')->set($data['licence']['organisation']['name']);
        $placeholder->getContainer('status')->set($data['status']['id']);
        $placeholder->getContainer('horizontalNavigationId')->set('application');

        $right = new ViewModel();
        $right->setTemplate('sections/application/partials/right');

        $placeholder->getContainer('right')->set($right);
    }
}
