<?php

/**
 * Cookie Listener
 */
namespace Olcs\Mvc;

use Olcs\Service\Cookie\CookieReader;
use Olcs\Service\Cookie\Preferences;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\Placeholder;

class CookieListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /** @var CookieReader */
    private $cookieReader;

    /** @var Placeholder */
    private $placeholder;

    /**
     * Create service instance
     *
     * @param CookieReader $cookieReader
     * @param Placeholder $placeholder
     *
     * @return CookieListener
     */
    public function __construct(
        CookieReader $cookieReader,
        Placeholder $placeholder
    ) {
        $this->cookieReader = $cookieReader;
        $this->placeholder = $placeholder;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();

        if (!($request instanceof HttpRequest)) {
            return;
        }

        $cookie = $request->getCookie();
        $cookieState = $this->cookieReader->getState($cookie);

        $this->placeholder->getContainer('cookieAnalytics')->set($cookieState->isActive(Preferences::KEY_ANALYTICS));
    }
}
