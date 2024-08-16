<?php

namespace Dvsa\Olcs\Api\Assertion\User;

use LmcRbacMvc\Assertion\AssertionInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * Check that the contact user belongs to the same organisation as the current user
 */
class ReadUserSelfserve implements AssertionInterface
{
    /**
     * Check that the contact user belongs to the same organisation as the current user
     *
     * @param AuthorizationService $authorizationService
     * @param User $context
     * @return bool
     */
    public function assert(AuthorizationService $authorizationService, User $context = null)
    {
        $currentUser = $authorizationService->getIdentity()->getUser();

        return (
            !$currentUser->getOrganisationUsers()->isEmpty()
            && !$context->getOrganisationUsers()->isEmpty()
            && (
                $currentUser->getOrganisationUsers()->first()->getOrganisation()->getId()
                === $context->getOrganisationUsers()->first()->getOrganisation()->getId()
            )
        );
    }
}
