<?php

namespace Dvsa\Olcs\CompaniesHouse;

/**
 * Companies House Module
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Module
{
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
