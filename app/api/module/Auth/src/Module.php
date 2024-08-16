<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth;

class Module
{
    /**
     * @return array
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Empty on purpose to defer loading to composer
     */
    public function getAutoloaderConfig(): void
    {
    }
}
