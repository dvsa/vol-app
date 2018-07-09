<?php
namespace Olcs\Controller\Initializer;

use Olcs\Controller\Listener\Navigation as NavigationListener;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Navigation
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Navigation implements InitializerInterface
{
    /**
     * attach the navigation listener
     *
     * @param mixed                   $instance
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return void
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator): void
    {
        $navigationListener = $serviceLocator->getServiceLocator()->get(NavigationListener::class);
        $instance->getEventManager()->attach($navigationListener);
    }
}
