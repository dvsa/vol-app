<?php

namespace Olcs\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

class StaticAssetsController extends AbstractActionController
{
    public function __construct(private array $config)
    {
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
        $fullPath = $prefix ? trim((string) $prefix, '/') . '/' . ltrim((string) $path, '/') : $path;

        // Build the redirect URL
        $redirectUrl = rtrim((string) $assetsUrl, '/') . '/' . ltrim((string) $fullPath, '/');

        // Return a 302 redirect
        return $this->redirect()->toUrl($redirectUrl);
    }
}
