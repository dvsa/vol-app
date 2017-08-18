<?php

namespace Olcs\Listener;

use Common\Rbac\IdentityProvider;
use Common\Rbac\User;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class NavigationToggle
 * @package Olcs\Listener
 */
class NavigationToggle implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * @var Navigation
     */
    protected $navigation;

    /**
     * @var IdentityProvider
     */
    protected $authenticationService;

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
        /** @var AuthenticationService $identity */
        $identity = $this->authenticationService->getIdentity();
        /** @var User $userData */
        $userData = $identity->getUserData();

        $disableDataRetentionRecords = true;

        if (isset($userData['disableDataRetentionRecords'])) {
            $disableDataRetentionRecords = !$userData['disableDataRetentionRecords'];
        }

        $this->navigation->findBy('id', 'admin-dashboard/admin-data-retention')
            ->setVisible($disableDataRetentionRecords);
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return HeaderSearch
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->navigation = $serviceLocator->get('navigation');
        $this->authenticationService = $serviceLocator->get('Common\Rbac\IdentityProvider');

        return $this;
    }
}
