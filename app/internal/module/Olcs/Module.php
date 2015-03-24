<?php

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */
namespace Olcs;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */
class Module implements ConsoleUsageProviderInterface
{
    /**
     * Display CLI usage
     *
     * @param ConsoleAdapterInterface $console
     *
     * @return array
     */
    public function getConsoleUsage(ConsoleAdapterInterface $console)
    {
        return array(
            // Describe available commands
                'batch-licence-status [--verbose|-v]' => 'Process licence status change rules',
            // Describe expected parameters
            array( '--verbose|-v', '(optional) turn on verbose mode'),
        );
    }

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
