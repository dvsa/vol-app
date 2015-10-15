<?php

/**
 * Cookie Banner Listener
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Mvc;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\Http\Request as HttpRequest;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Cookie Banner Listener
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookieBannerListener implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * @var CookieBanner
     */
    protected $cookieBanner;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->cookieBanner = $serviceLocator->get('CookieBanner');
        return $this;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], $priority);
    }

    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();

        if (!($request instanceof HttpRequest)) {
            return;
        }

        $this->cookieBanner->toSeeOrNotToSee();
    }
}
