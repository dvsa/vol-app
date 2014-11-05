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
    protected $triggeredEvents = [];

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Adds event to the list of triggered events
     *
     * @param string $event
     * @return $this
     */
    public function addTriggeredEvent($event)
    {
        $this->triggeredEvents[$event] = true;
        return $this;
    }

    /**
     * Checks whether an event has already been triggered
     *
     * @param string $event
     * @return bool
     */
    public function hasEventBeenTriggered($event)
    {
        if (isset($this->triggeredEvents[$event])) {
            return true;
        }

        return false;
    }

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
        $this->setParams($e->getRouteMatch()->getParams());

        foreach ($this->getParams() as $key => $value) {
            $this->trigger($key, $value);
        }
    }

    /**
     * @param $event
     * @param $value
     */
    public function trigger($event, $value)
    {
        if ($this->hasEventBeenTriggered($event)) {
            return false;
        }

        $this->addTriggeredEvent($event);

        $e = new RouteParam();
        $e->setContext($this->getParams())
          ->setValue($value)
          ->setTarget($this);

        $this->getEventManager()->trigger(self::EVENT_PARAM . $event, $e);
    }
}
