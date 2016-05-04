<?php

/**
 * Cli Module
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Cli;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;

/**
 * Cli Module
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Module implements ConsoleUsageProviderInterface
{
    /**
     * Display CLI usage
     *
     * @param ConsoleAdapterInterface $console
     *
     * @return array
     * @inheritdoc
     */
    public function getConsoleUsage(ConsoleAdapterInterface $console)
    {
        return array(
            'create-translation-csv' => 'Create a translation CSV to be used for getting Welsh translations',
            array( '<source>', 'Source file containing a list of translation keys'),
            array( '<destination>', 'CSV file to be generated'),
        );
    }

    /**
     * @inheritDoc
     */
    public function onBootstrap(MvcEvent $event)
    {
        // block session saving, as it is unnecessary for cli
        $handler = new \Cli\Session\NullSaveHandler();
        $manager = new \Zend\Session\SessionManager();
        $manager->setSaveHandler($handler);
        \Zend\Session\Container::setDefaultManager($manager);
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
