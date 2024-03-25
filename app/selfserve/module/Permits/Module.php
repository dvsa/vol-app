<?php

namespace Permits;

class Module
{
    /**
     * @return string[][][]
     *
     * @psalm-return array{'Laminas\\Loader\\StandardAutoloader'::class: array{namespaces: array{Permits: '/home/andy/olcs/olcs-selfserve/module/Permits/src/'}}}
     */
    public function getAutoloaderConfig(): array
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
