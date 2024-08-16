<?php

namespace Dvsa\Olcs\Db;

use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;

/**
 * Module class
 */
class Module implements BootstrapListenerInterface
{
    public function onBootstrap(EventInterface $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        /** @var MvcEvent $e */
        $eventManager = $e->getApplication()->getEventManager();

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager, 1);

        // Enable and configure Doctrine filters
        $entityManager = $sm->get('doctrine.entitymanager.orm_default');
        $entityManager->getFilters()->enable('soft-deleteable');
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
