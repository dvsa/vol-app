<?php

namespace Dvsa\Olcs\Scanning;

/**
 * Scanning Module
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Module
{
    public function getConfig()
    {
        $base = include __DIR__ . '/../config/module.config.php';
        return $base;
    }

    public function getAutoloaderConfig()
    {
        return [
            \Laminas\Loader\StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ ,
                ],
            ],
        ];
    }
}
