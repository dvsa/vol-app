<?php

namespace Permits;

class Module
{
    public function getAutoloaderConfig()
    {
        return [
            \Laminas\Loader\StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/',
                ],
            ],
        ];
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
