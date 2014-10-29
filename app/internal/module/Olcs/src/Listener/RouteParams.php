<?php

namespace Olcs\Listener;

use Olcs\Event\RouteParam;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;

/**
 * Class RouteParams
 * @package Olcs\Listener
 */
class RouteParams implements EventManagerAwareInterface, ListenerAggregateInterface
{
    use EventManagerAwareTrait;
    use ListenerAggregateTrait;

    const EVENT_PARAM = 'route.param.';

    /**
     * @var array
     */
    protected $params = [];

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
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), 20);
    }

    /**
     * @param MvcEvent $e
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->params = $e->getRouteMatch()->getParams();

        foreach ($this->params as $key => $value) {
            $this->trigger($key, $value);
        }
    }

    /**
     * @param $event
     * @param $value
     */
    public function trigger($event, $value)
    {
        $e = new RouteParam();
        $e->setContext($this->params)
          ->setValue($value)
          ->setTarget($this);

        $this->getEventManager()->trigger(self::EVENT_PARAM . $event, $e);
    }
}
