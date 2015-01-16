<?php

/**
 * Variation Utility
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Service\Utility;

use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Utility\VariationUtilityInterface;

/**
 * Variation Utility
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationUtility implements VariationUtilityInterface
{
    /**
     * Alter the create variation data
     *
     * @param array $data
     * @return array
     */
    public function alterCreateVariationData(array $data)
    {
        $data['status'] = ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION;

        return $data;
    }
}
