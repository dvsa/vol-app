<?php

namespace Olcs\Assertion\Ebsr;

use LmcRbacMvc\Assertion\AssertionInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Check that the current user can access the EBSR upload page
 *
 * The selfserve-ebsr-upload permission is held by the operator-admin, operator-user and operator-tc
 * roles, so on its own it only answers "does this user hold an operator role". Uploading also needs
 * a licence able to accept EBSR submissions, which is the rule the pack is held to once it reaches
 * processing.
 *
 * Deliberately does not test the user type. User::getUserType() returns transport-manager for any
 * user linked to a transport manager record, whatever role they hold, and those roles include the
 * operator ones - so an operator admin who is also a transport manager is typed transport-manager
 * and would be wrongly refused. Restricting this to operators is already the permission's job.
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

        return (($currentUser->getUserData()['hasEbsrEligibleLicence'] ?? false) === true);
    }
}
