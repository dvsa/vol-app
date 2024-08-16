<?php

namespace Dvsa\Olcs\Api\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Rbac\Role\RoleInterface;

/**
 * Role Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="role",
 *    indexes={
 *        @ORM\Index(name="ix_role_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_role_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class Role extends AbstractRole implements RoleInterface
{
    public const ROLE_SYSTEM_ADMIN = 'system-admin';
    public const ROLE_INTERNAL_LIMITED_READ_ONLY = 'internal-limited-read-only';
    public const ROLE_INTERNAL_READ_ONLY = 'internal-read-only';
    public const ROLE_INTERNAL_CASE_WORKER = 'internal-case-worker';
    public const ROLE_INTERNAL_ADMIN = 'internal-admin';
    public const ROLE_INTERNAL_IRHP_ADMIN = 'internal-irhp-admin';
    public const ROLE_OPERATOR_ADMIN = 'operator-admin';
    public const ROLE_OPERATOR_USER = 'operator-user';
    public const ROLE_OPERATOR_TM = 'operator-tm';
    public const ROLE_PARTNER_ADMIN = 'partner-admin';
    public const ROLE_PARTNER_USER = 'partner-user';
    public const ROLE_LOCAL_AUTHORITY_ADMIN = 'local-authority-admin';
    public const ROLE_LOCAL_AUTHORITY_USER = 'local-authority-user';
    public const ROLE_ANON = 'anon';

    /**
     * List of roles and the roles they are allowed to update a user from/to
     *
     * @var array
     */
    private static $rolesHierarchy = [
        self::ROLE_SYSTEM_ADMIN => [
            self::ROLE_SYSTEM_ADMIN,
            self::ROLE_INTERNAL_ADMIN,
            self::ROLE_INTERNAL_IRHP_ADMIN,
            self::ROLE_INTERNAL_CASE_WORKER,
            self::ROLE_INTERNAL_READ_ONLY,
            self::ROLE_INTERNAL_LIMITED_READ_ONLY,
            self::ROLE_OPERATOR_ADMIN,
            self::ROLE_OPERATOR_USER,
            self::ROLE_OPERATOR_TM,
            self::ROLE_PARTNER_ADMIN,
            self::ROLE_PARTNER_USER,
            self::ROLE_LOCAL_AUTHORITY_ADMIN,
            self::ROLE_LOCAL_AUTHORITY_USER,
        ],
        self::ROLE_INTERNAL_ADMIN => [
            self::ROLE_INTERNAL_ADMIN,
            self::ROLE_INTERNAL_IRHP_ADMIN,
            self::ROLE_INTERNAL_CASE_WORKER,
            self::ROLE_INTERNAL_READ_ONLY,
            self::ROLE_INTERNAL_LIMITED_READ_ONLY,
            self::ROLE_OPERATOR_ADMIN,
            self::ROLE_OPERATOR_USER,
            self::ROLE_OPERATOR_TM,
            self::ROLE_PARTNER_ADMIN,
            self::ROLE_PARTNER_USER,
            self::ROLE_LOCAL_AUTHORITY_ADMIN,
            self::ROLE_LOCAL_AUTHORITY_USER,
        ],
        self::ROLE_INTERNAL_IRHP_ADMIN => [
            self::ROLE_INTERNAL_ADMIN,
            self::ROLE_INTERNAL_IRHP_ADMIN,
            self::ROLE_INTERNAL_CASE_WORKER,
            self::ROLE_INTERNAL_READ_ONLY,
            self::ROLE_INTERNAL_LIMITED_READ_ONLY,
            self::ROLE_OPERATOR_ADMIN,
            self::ROLE_OPERATOR_USER,
            self::ROLE_OPERATOR_TM,
            self::ROLE_PARTNER_ADMIN,
            self::ROLE_PARTNER_USER,
            self::ROLE_LOCAL_AUTHORITY_ADMIN,
            self::ROLE_LOCAL_AUTHORITY_USER,
        ],
        self::ROLE_INTERNAL_CASE_WORKER => [
            self::ROLE_INTERNAL_CASE_WORKER,
            self::ROLE_INTERNAL_READ_ONLY,
            self::ROLE_INTERNAL_LIMITED_READ_ONLY,
            self::ROLE_OPERATOR_ADMIN,
            self::ROLE_OPERATOR_USER,
            self::ROLE_OPERATOR_TM,
            self::ROLE_PARTNER_ADMIN,
            self::ROLE_PARTNER_USER,
            self::ROLE_LOCAL_AUTHORITY_ADMIN,
            self::ROLE_LOCAL_AUTHORITY_USER,
        ],
    ];

    /**
     * Get list of roles this role is allowed to switch any user to
     *
     * @return array
     */
    public function getAllowedRoles()
    {
        return self::$rolesHierarchy[$this->getRole()] ?? [];
    }

    public static function anon()
    {
        $role = new self();
        $role->setId(self::ROLE_ANON);
        $role->setRole(self::ROLE_ANON);

        return $role;
    }

    /**
     * Get the name of the role.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getRole();
    }

    /**
     * Checks if a permission exists for this role (it does not check child roles)
     *
     * @param  mixed $permission
     * @return bool
     */
    public function hasPermission($permission)
    {

        /** @var RolePermission $rolePermission */
        foreach ($this->getRolePermissions() as $rolePermission) {
            if ($rolePermission->getPermission()->getName() === $permission) {
                return true;
            }
        }

        return false;
    }
}
