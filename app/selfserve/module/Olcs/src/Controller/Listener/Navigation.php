<?php

namespace Olcs\Controller\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\Navigation\Navigation as ZendNavigation;
use Common\Service\Cqrs\Query\QuerySender;
use Common\FeatureToggle;

/**
 * Class Navigation
 * @author Ian Lindsay <ian@hemera-business-services.co.uk
 */
class Navigation implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var ZendNavigation
     */
    protected $navigation;

    /**
     * @var QuerySender
     */
    protected $querySender;

    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * Navigation constructor
     *
     * @param ZendNavigation $navigation
     * @param QuerySender    $querySender
     *
     * @return void
     */
    public function __construct(ZendNavigation $navigation, QuerySender $querySender)
    {
        $this->navigation = $navigation;
        $this->querySender = $querySender;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events Events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), 20);
    }

    /**
     * onDispatch - set feature toggle rules here for navigation
     *
     * @param MvcEvent $e Event
     *
     * @return void
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->toggleEcmtMenus();
        $this->togglePermitsMenus();
    }

    /**
     * Toggle ECMT menus
     */
    private function toggleEcmtMenus(): void
    {
        $ecmtEnabled = $this->querySender->featuresEnabled([FeatureToggle::SELFSERVE_ECMT]);
        $this->navigation->findBy('id', 'dashboard-permits')->setVisible($ecmtEnabled);
    }

    /**
     * Toggle EU permits menus
     */
    private function togglePermitsMenus(): void
    {
        //permits related config will go here once available
        //$permitsEnabled = $this->querySender->featuresEnabled([FeatureToggle::SELFSERVE_PERMITS]);
    }

}