<?php

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */
namespace Olcs;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */
class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');
        $placeholder = $viewHelperManager->get('placeholder');

        $placeholder->getContainer('pageTitle')->setSeparator(' / ');
        $placeholder->getContainer('pageSubtitle')->setSeparator(' / ');

        $headTitleHelper = $viewHelperManager->get('headTitle');
        $headTitleHelper->setSeparator(' - ');
        $headTitleHelper->append('Olcs');

        $listener = $e->getApplication()->getServiceManager()->get('Common\Rbac\Navigation\IsAllowedListener');

        $events = $e->getApplication()->getEventManager();

        $events->getSharedManager()
            ->attach('Zend\View\Helper\Navigation\AbstractHelper', 'isAllowed', array($listener, 'accept'));
        $events->attach(
            $e->getApplication()->getServiceManager()->get('ZfcRbac\View\Strategy\UnauthorizedStrategy')
        );
        $events->attach(
            $e->getApplication()->getServiceManager()->get('ZfcRbac\View\Strategy\RedirectStrategy')
        );
    }

    public function getConfig()
    {
        $base = include __DIR__ . '/config/module.config.php';
        return $base;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/',
                ),
            ),
        );
    }
}
