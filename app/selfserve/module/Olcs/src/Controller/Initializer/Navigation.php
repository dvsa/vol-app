<?php
namespace Olcs\Controller\Initializer;

use Olcs\Controller\Listener\Navigation as NavigationListener;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Auth\Controller\LoginController;

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
        /**
         * don't need the navigation listener on the login page (and also need to prevent unauthenticated requests)
         */
        if (!$instance instanceof LoginController) {
            $navigationListener = $serviceLocator->getServiceLocator()->get(NavigationListener::class);
            $instance->getEventManager()->attach($navigationListener);
        }
    }
}
