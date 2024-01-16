<?php
namespace Olcs\Controller\Initializer;

use Dvsa\Olcs\Auth\Controller\LoginController;
use Interop\Container\ContainerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Olcs\Controller\Listener\Navigation as NavigationListener;

/**
 * Class Navigation
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Navigation implements InitializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        /**
         * don't need the navigation listener on the login page (and also need to prevent unauthenticated requests)
         */
        if (!$instance instanceof LoginController) {
            $navigationListener = $container->get(NavigationListener::class);
            $instance->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, [$navigationListener, 'onDispatch']);
        }
    }
}
