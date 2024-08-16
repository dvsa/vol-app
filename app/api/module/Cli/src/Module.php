<?php

namespace Dvsa\Olcs\Cli;

/**
 * Cli Module
 *
 * @codeCoverageIgnore
 */
class Module
{
    /**
     * On bootstrap
     *
     * @return void
     */
    public function onBootstrap()
    {
        // block session saving when running cli, as causes permissions errors
        // also block session saving when running api to avoid unnecessary creation of surplus sessions

        $handler = new Session\NullSaveHandler();
        $manager = new \Laminas\Session\SessionManager();
        $manager->setSaveHandler($handler);
        \Laminas\Session\Container::setDefaultManager($manager);
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        return (include __DIR__ . '/../config/module.config.php');
    }

    /**
     * Get autoloader config
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            \Laminas\Loader\StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__,
                ],
            ],
        ];
    }
}
