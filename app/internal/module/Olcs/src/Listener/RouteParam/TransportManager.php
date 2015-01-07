<?php

namespace Olcs\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Service\Data\GenericAwareTrait as GenericSeviceAwareTrait;

/**
 * Class Cases
 * @package Olcs\Listener\RouteParam
 */
class TransportManager implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;
    use GenericSeviceAwareTrait;
    use ViewHelperManagerAwareTrait;

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
            RouteParams::EVENT_PARAM . 'transportManager', [$this, 'onTransportManager'], 1
        );
    }

    /**
     * @param RouteParam $e
     */
    public function onTransportManager(RouteParam $e)
    {
        $id = $e->getValue();

        $data = $this->getGenericService()->fetchOne($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('transportManager')->set($data);

        $this->doTitles($data);
    }

    public function doTitles($data)
    {
        $pageTitle = $data['contactDetails']['person']['forename'] . ' ';
        $pageTitle .= $data['contactDetails']['person']['familyName'];

        $this->getViewHelperManager()->get('placeholder')->getContainer('pageTitle')->append($pageTitle);
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

        $this->setGenericService(
            $serviceLocator->get('DataServiceManager')->get('Generic\Service\Data\TransportManager')
        );

        return $this;
    }
}
