<?php

namespace Olcs\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

class StaticAssetsController extends AbstractActionController
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function redirectAction()
    {
        // Get the captured path from the route
        $path = $this->params()->fromRoute('path', '');

        // Get optional prefix (e.g., 'styles' for /styles/* routes)
        $prefix = $this->params()->fromRoute('prefix', '');

        // Get the assets URL from config
        $assetsUrl = $this->config['assets']['base_url'] ?? null;

        // Throw exception if no CDN URL configured
        if (empty($assetsUrl)) {
            throw new \RuntimeException('CDN URL not configured. Static assets require assets.base_url in configuration.');
        }

        // Build the full path with optional prefix
        $fullPath = $prefix ? trim($prefix, '/') . '/' . ltrim($path, '/') : $path;

        // Build the redirect URL
        $redirectUrl = rtrim($assetsUrl, '/') . '/' . ltrim($fullPath, '/');

        // Return a 302 redirect
        return $this->redirect()->toUrl($redirectUrl);
    }
}
