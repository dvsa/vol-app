<?php

namespace Dvsa\Olcs\Api\Assertion\User;

use LmcRbacMvc\Assertion\AssertionInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * Check whether the current user can manage a user via selfserve
 */
class ManageUserSelfserve implements AssertionInterface
{
    public function assert(AuthorizationService $authorizationService, User $context = null)
    {
        if (!isset($context)) {
            // nothing to check against (possibly called while creating a new user)
            // and the current user has a role which can manage users (otherwise it should never get that far)
            return true;
        }
        return match ($context->getUserType()) {
            User::USER_TYPE_PARTNER => $this->canManagePartner($authorizationService, $context),
            User::USER_TYPE_LOCAL_AUTHORITY => $this->canManageLocalAuthority($authorizationService, $context),
            User::USER_TYPE_OPERATOR, User::USER_TYPE_TRANSPORT_MANAGER => $this->canManageOperator($authorizationService, $context),
            default => false,
        };
    }

    private function canManagePartner(AuthorizationService $authorizationService, User $context)
    {
        $currentUser = $authorizationService->getIdentity()->getUser();

        if (
            $authorizationService->isGranted(Permission::PARTNER_ADMIN)
            && ($currentUser->getUserType() === $context->getUserType())
            && ($currentUser->getPartnerContactDetails()->getId()
                === $context->getPartnerContactDetails()->getId()
            )
        ) {
            // has related admin permission
            // and manages the same type of user
            // and is linked to the same entity
            return true;
        }

        return false;
    }

    private function canManageLocalAuthority(AuthorizationService $authorizationService, User $context)
    {
        $currentUser = $authorizationService->getIdentity()->getUser();

        if (
            $authorizationService->isGranted(Permission::LOCAL_AUTHORITY_ADMIN)
            && ($currentUser->getUserType() === $context->getUserType())
            && ($currentUser->getLocalAuthority()->getId() === $context->getLocalAuthority()->getId())
        ) {
            // has related admin permission
            // and manages the same type of user
            // and is linked to the same entity
            return true;
        }

        return false;
    }

    private function canManageOperator(AuthorizationService $authorizationService, User $context)
    {
        if (
            ($authorizationService->isGranted(Permission::OPERATOR_ADMIN) || $authorizationService->isGranted(Permission::OPERATOR_TC))
            && $authorizationService->isGranted(Permission::CAN_READ_USER_SELFSERVE, $context)
        ) {
            // has related admin permission
            // and is linked to the same entity
            // Note
            // only the first org is checked as it is one2one - even though the DB is designed as many2many
            // this will have to be changed once it becomes a real many2many - rules unknown atm
            return true;
        }

        return false;
    }
}
