<?php

/**
 * Application Utility
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Service\Utility;

use Common\Service\Entity\ApplicationEntityService;
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
        $data['status'] = ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION;

        return $data;
    }
}
