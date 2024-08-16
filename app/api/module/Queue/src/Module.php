<?php

namespace Dvsa\Olcs\Queue;

/**
 * Class Module
 *
 * @package Dvsa\Olcs\Queue
 */
class Module
{
    /**
     * @codeCoverageIgnore
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getAutoloaderConfig()
    {
    }
}
