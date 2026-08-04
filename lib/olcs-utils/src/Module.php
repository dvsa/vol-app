<?php

/**
 * Module
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Utils;

/**
 * Module
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Module
{
    /**
     * Get module config
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
