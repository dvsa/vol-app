<?php

declare(strict_types=1);

namespace Application;

class Module
{
    protected const MODULE_ROUTES = ['create_application', 'lva-application'];

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $serviceConfig = include __DIR__ . '/config/service.config.php';
        $controllerConfig = include __DIR__ . '/config/controller.config.php';
        $viewConfig = include __DIR__ . '/config/view.config.php';
        $validationConfig = include __DIR__ . '/config/validation.config.php';
        return [
            'router' => $this->loadRouterConfiguration(),
            'service_manager' => $serviceConfig['plugins'],
            'controllers' => $controllerConfig['plugins'],
            'zfc_rbac' => include __DIR__ . '/config/authorization.config.php',
            'view_manager' => $viewConfig['listener'],
            'view_helpers' => $viewConfig['plugins'],
            'validators' => $validationConfig['plugins'],
        ];
    }

    /**
     * @return array
     */
    protected function loadRouterConfiguration(): array
    {
        $routerConfiguration = include __DIR__ . '/config/router.config.php';

        // Only register the approved top level module routes
        $routerConfiguration['routes'] = array_intersect_key($routerConfiguration['routes'], array_flip(static::MODULE_ROUTES));

        return $routerConfiguration;
    }
}
