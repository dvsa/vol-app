<?php

/**
 * Module
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot;

use Laminas\Cache\Storage\Adapter\Redis;
use Laminas\I18n\Translator\Translator;

/**
 * Module
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Module
{
    public function onBootstrap(\Laminas\Mvc\MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        $translator = $sm->get('translator');
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

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
