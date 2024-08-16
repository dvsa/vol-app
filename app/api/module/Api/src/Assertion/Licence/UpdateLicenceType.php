<?php

namespace Dvsa\Olcs\Api\Assertion\Licence;

use LmcRbacMvc\Assertion\AssertionInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Check whether the current user can update the type of licence for the given licence
 */
class UpdateLicenceType implements AssertionInterface
{
    public function assert(AuthorizationService $authorizationService, Licence $context = null)
    {
        if ($authorizationService->isGranted(Permission::INTERNAL_USER)) {
            return true;
        }

        if ($context->getGoodsOrPsv()->getId() === Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return true;
        }

        $allowedLicTypes = [Licence::LICENCE_TYPE_STANDARD_NATIONAL, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL];

        if (in_array($context->getLicenceType()->getId(), $allowedLicTypes)) {
            return true;
        }

        return false;
    }
}
