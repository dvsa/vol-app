<?php

/**
 * Application Utility Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Utility;

/**
 * Application Utility Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface ApplicationUtilityInterface
{
    /**
     * Alter the create application data
     *
     * @return array
     */
    public function alterCreateApplicationData(array $data);
}
