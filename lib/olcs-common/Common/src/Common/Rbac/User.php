<?php

namespace Common\Rbac;

use LmcRbacMvc\Identity\IdentityInterface;

/**
 * Class User
 * @package Common\Rbac
 */
class User implements IdentityInterface
{
    public const USER_TYPE_INTERNAL = 'internal';

    public const USER_TYPE_ANON = 'anon';

    public const USER_TYPE_LOCAL_AUTHORITY = 'local-authority';

    public const USER_TYPE_OPERATOR = 'operator';

    public const USER_TYPE_PARTNER = 'partner';

    public const USER_TYPE_TRANSPORT_MANAGER = 'transport-manager';

    public const USER_TYPE_NOT_IDENTIFIED = 'not-identified';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $pid;

    /**
     * @var string
     */
    protected $userType;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var array
     */
    protected $roles = [];

    /**
     * @var array
     */
    protected $userData = [];

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id id
     */
    public function setId($id): void
    {
        $this->id = (int) $id;
    }

    /**
     * Get pid.
     *
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set pid.
     *
     * @param string $pid pid
     */
    public function setPid($pid): void
    {
        $this->pid = $pid;
    }

    /**
     * Get user type.
     *
     * @return string
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * Set user type.
     *
     * @param string $userType user type
     */
    public function setUserType($userType): void
    {
        $this->userType = $userType;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * Get Roles
     *
     * @return array
     */
    #[\Override]
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set Roles
     *
     * @param array $roles roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Get User data
     *
     * @return array
     */
    public function getUserData()
    {
        return $this->userData;
    }

    /**
     * Set user data
     *
     * @param array $userData user data
     */
    public function setUserData($userData): void
    {
        $this->userData = $userData;
    }

    /**
     * Checks if it is an anonymous user
     *
     * @return bool
     */
    public function isAnonymous()
    {
        return (empty($this->userType) || ($this->userType === self::USER_TYPE_ANON));
    }

    /**
     * Checks if user is local authority
     *
     * @return bool
     */
    public function isLocalAuthority()
    {
        return $this->userType === self::USER_TYPE_LOCAL_AUTHORITY;
    }

    /**
     * Checks if user could not be verified
     *
     * @return bool
     */
    public function isNotIdentified()
    {
        return ($this->userType === self::USER_TYPE_NOT_IDENTIFIED);
    }

    public function hasRole($role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    public function hasAgreedTerms(): bool
    {
        return $this->userData['termsAgreed'];
    }
}
