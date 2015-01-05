<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\Navigation\PluginManager as ViewHelperManager;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;

/**
 * Class Cases
 * @package Olcs\Listener\RouteParam
 */
class BusRegId implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use ViewHelperManagerAwareTrait;
    use ServiceLocatorAwareTrait;

    protected $busRegService;

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
        $this->listeners[] = $events->attach(RouteParams::EVENT_PARAM . 'busRegId', [$this, 'onBusRegId'], 1);
    }

    /**
     * @param RouteParam $e
     */
    public function onBusRegId(RouteParam $e)
    {
        $context = $e->getContext();
        $urlPlugin = $this->getViewHelperManager()->get('Url');
        $busReg = $this->getBusRegService()->fetchOne($e->getValue());

        $licUrl = $urlPlugin->__invoke('licence/bus', ['licence' => $busReg['licence']['id']], [], true);
        $title = '<a href="' . $licUrl . '">' . $busReg['licence']['licNo'] . '</a>' . '/' . $busReg['routeNo'];

        $subTitle = $busReg['licence']['organisation']['name']
                  . ', Variation '
                  . $busReg['variationNo'];

        $this->getViewHelperManager()->get('headTitle')->prepend($busReg['regNo']);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('pageTitle')->append($title);
        $placeholder->getContainer('pageSubtitle')->append($subTitle);
    }

    /**
     * @return mixed
     */
    public function getBusRegService()
    {
        return $this->busRegService;
    }

    /**
     * @param mixed $busRegService
     */
    public function setBusRegService($busRegService)
    {
        $this->busRegService = $busRegService;
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
        $this->setServiceLocator($serviceLocator);

        $this->setBusRegService($serviceLocator->get('DataServiceManager')->get('Generic\Service\Data\BusReg'));

        return $this;
    }
}
