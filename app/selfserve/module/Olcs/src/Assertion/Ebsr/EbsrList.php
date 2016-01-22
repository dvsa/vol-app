<?php

namespace Olcs\Assertion\Ebsr;

use Common\Rbac\User;
use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Service\AuthorizationService;

/**
 * Check that the current user can access EBSR list
 */
class EbsrList implements AssertionInterface
{
    /**
     * Check that the current user can access EBSR list
     *
     * @param AuthorizationService $authorizationService
     * @return bool
     */
    public function assert(AuthorizationService $authorizationService)
    {
        $currentUser = $authorizationService->getIdentity();

        return (
            ($currentUser->getUserType() === User::USER_TYPE_LOCAL_AUTHORITY)
            || (!empty($currentUser->getUserData()['hasActivePsvLicence'])
                && ($currentUser->getUserData()['hasActivePsvLicence'] === true)
            )
        );
    }
}
