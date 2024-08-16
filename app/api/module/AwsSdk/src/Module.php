<?php

namespace Dvsa\Olcs\AwsSdk;

/**
 * Class Module
 *
 * @package Dvsa\Olcs\AwsSdk
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
     * Empty on purpose to defer loading to composer
     * @codeCoverageIgnore No value in testing an empty method
     */
    public function getAutoloaderConfig()
    {
    }
}
