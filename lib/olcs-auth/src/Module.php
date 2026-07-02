<?php

/**
 * Authentication Module
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Auth;

/**
 * Authentication Module
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Module
{
    /**
     * Get module config
     *
     * @return array
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
