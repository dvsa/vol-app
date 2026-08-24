<?php

namespace Olcs\Logging\Mvc;

use Laminas\Mvc\MvcEvent;
use Olcs\Logging\Module as LoggingModule;

/**
 * Laminas MVC bindings for olcs-logging.
 *
 * Everything here is framework-coupled by nature: the request/error listeners
 * hang off MvcEvent, and bootstrapping runs off the MVC bootstrap event. Keeping
 * them in their own package is what lets olcs-logging itself stay free of
 * laminas-mvc, which is capped at PHP 8.4 and discontinued.
 *
 * Register this after Olcs\Logging so its config merges on top.
 */
class Module
{
    public function getConfig(): array
    {
        return [
            'listeners' => [
                Listener\LogRequest::class,
                Listener\LogError::class,
            ],
            'service_manager' => [
                'factories' => [
                    Listener\LogRequest::class => Listener\LogRequest::class,
                    Listener\LogError::class => Listener\LogError::class,
                ],
            ],
        ];
    }

    public function onBootstrap(MvcEvent $event): void
    {
        $container = $event->getApplication()->getServiceManager();

        new LoggingModule()->bootstrapLogger($container, $container->get('Config'));
    }
}
