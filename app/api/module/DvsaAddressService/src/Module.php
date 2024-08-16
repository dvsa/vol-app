<?php

namespace Dvsa\Olcs\DvsaAddressService;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Empty on purpose to defer loading to composer
     */
    public function getAutoloaderConfig()
    {
    }
}
