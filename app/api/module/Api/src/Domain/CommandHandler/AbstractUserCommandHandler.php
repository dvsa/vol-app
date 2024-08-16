<?php

/**
 * User Abstract
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\User\Role;
use Olcs\Logging\Log\Logger;

/**
 * User Abstract
 */
abstract class AbstractUserCommandHandler extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public const ERR_USERNAME_EXISTS = 'ERR_USERNAME_EXISTS';
    public const ERR_ROLES_PERMISSION = 'ERR_ROLES_PERMISSION';
    public const ERR_ROLES_PERMISSION_LAST_USER = 'ERR_ROLES_PERMISSION_LAST_USER';

    protected $repoServiceName = 'User';

    protected $usernameErrorKey = 'loginId';
    protected $roleErrorKey = 'role';

    /**
     * Validates username
     *
     * @return void
     * @throws RuntimeException
     * @throws ValidationException
     */
    protected function validateUsername(string $username): void
    {
        $repo = $this->getRepo();
        $repo->disableSoftDeleteable();
        $users = $repo->fetchByLoginId($username);
        $repo->enableSoftDeleteable();

        if (!empty($users)) {
            throw new ValidationException([$this->usernameErrorKey => [self::ERR_USERNAME_EXISTS]]);
        }
    }

    /**
     * Validates roles
     *
     * @param array $new     List of roles to change to
     * @param array $current List of existing roles the user has
     *
     * @return bool
     * @throws ValidationException
     */
    protected function validateRoles(array $new, array $current = null): bool
    {
        // convert to list of roles
        $current = isset($current) ? array_map(
            static fn(Role $role) => $role->getRole(),
            $current
        ) : [];

        // check if the current user's own roles let the user to
        // - change anyone who holds $current roles
        // - set anyone to $new roles
        // therefore combine the both lists together for this particular check
        $roles = array_merge($current, $new);

        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isAllowedToPerformActionOnRoles($roles)) {
            Logger::debug(
                'Roles validation error',
                [
                    'from' => $current,
                    'to' => $new,
                    'by' => array_map(
                        fn($role) => $role->getRole(),
                        $currentUser->getRoles()->toArray()
                    ),
                ]
            );

            throw new ValidationException([self::ERR_ROLES_PERMISSION]);
        }

        // check if ROLE_SYSTEM_ADMIN has been removed
        if (in_array(Role::ROLE_SYSTEM_ADMIN, $current) && !in_array(Role::ROLE_SYSTEM_ADMIN, $new)) {
            // make sure this is not the last user with ROLE_SYSTEM_ADMIN defined in the system
            $noOfUsers = $this->getRepo()->fetchUsersCountByRole(Role::ROLE_SYSTEM_ADMIN);

            if ($noOfUsers < 2) {
                // no one else left with this role
                throw new ValidationException([$this->roleErrorKey => [self::ERR_ROLES_PERMISSION_LAST_USER]]);
            }
        }

        return true;
    }
}
