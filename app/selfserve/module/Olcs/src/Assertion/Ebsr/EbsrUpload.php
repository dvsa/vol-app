<?php

namespace Olcs\Assertion\Ebsr;

use Common\Rbac\User;
use LmcRbacMvc\Assertion\AssertionInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Check that the current user can access the EBSR upload page
 *
 * The selfserve-ebsr-upload permission is held by every operator-admin and operator-user role, so
 * on its own it only answers "is this an operator". Uploading also needs a licence able to accept
 * EBSR submissions, which is the rule the pack is held to once it reaches processing.
 */
class EbsrUpload implements AssertionInterface
{
    /**
     * Check that the current user can access the EBSR upload page
     *
     * @param AuthorizationService $authorizationService
     * @return bool
     */
    #[\Override]
    public function assert(AuthorizationService $authorizationService)
    {
        $currentUser = $authorizationService->getIdentity();

        return (
            ($currentUser->getUserType() === User::USER_TYPE_OPERATOR)
            && (($currentUser->getUserData()['hasEbsrEligibleLicence'] ?? false) === true)
        );
    }
}
