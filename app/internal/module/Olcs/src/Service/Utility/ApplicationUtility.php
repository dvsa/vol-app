<?php

/**
 * Application Utility
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Service\Utility;

use Common\RefData;
use Common\Service\Utility\ApplicationUtilityInterface;

/**
 * Application Utility
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationUtility implements ApplicationUtilityInterface
{
    /**
     * Alter the create application data
     *
     * @param array $data
     * @return array
     */
    public function alterCreateApplicationData(array $data)
    {
        $data['status'] = RefData::APPLICATION_STATUS_UNDER_CONSIDERATION;

        return $data;
    }
}
