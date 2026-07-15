<?php

declare(strict_types=1);

namespace Common\Rbac\Service;

use Common\RefData;
use LmcRbacMvc\Service\AuthorizationService;

class Permission
{
    public function __construct(private AuthorizationService $authService)
    {
    }

    /**
     * Returns true if the user is internal read only, or internal limited read only.
     * Returns false for all other users
     */
    public function isInternalReadOnly(): bool
    {
        return $this->authService->isGranted(RefData::PERMISSION_INTERNAL_USER)
            && !$this->authService->isGranted(RefData::PERMISSION_INTERNAL_EDIT);
    }

    public function isGranted(string $permission, $context = null): bool
    {
        return $this->authService->isGranted($permission, $context);
    }

    public function isSelf(string $userId): bool
    {
        $userData = $this->authService->getIdentity()->getUserData();

        $currentUserId = $userData['id'] ?? null;

        if ($currentUserId === null) {
            return false;
        }

        return (string) $currentUserId === $userId;
    }

    public function canManageSelfserveUsers(): bool
    {
        return $this->authService->isGranted(RefData::PERMISSION_CAN_MANAGE_USER_SELFSERVE);
    }

    /**
     * $role, is the role of the user being deleted, as opposed to the role of the user doing the deleting
     */
    public function canRemoveSelfserveUser(string $userId, string $role): bool
    {
        /**
         * we have to check the auth service to see if operator admins can be deleted,
         * since this needs calculating at an organisation level
         *
         * what we're preventing here is the last operator admin being deleted
         */
        if ($role === RefData::ROLE_OPERATOR_ADMIN && !$this->authService->getIdentity()->getUserData()['canDeleteOperatorAdmin']) {
            return false;
        }

        return $this->canManageSelfserveUsers() && !$this->isSelf($userId);
    }
}
