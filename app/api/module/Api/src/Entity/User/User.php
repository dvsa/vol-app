<?php

namespace Dvsa\Olcs\Api\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as OrganisationUserEntity;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\User\Role as RoleEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;

/**
 * User Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="user",
 *    indexes={
 *        @ORM\Index(name="ix_user_team_id", columns={"team_id"}),
 *        @ORM\Index(name="ix_user_local_authority_id", columns={"local_authority_id"}),
 *        @ORM\Index(name="ix_user_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_user_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_user_contact_details_id", columns={"contact_details_id"}),
 *        @ORM\Index(name="ix_user_partner_contact_details_id", columns={"partner_contact_details_id"}),
 *        @ORM\Index(name="ix_user_transport_manager_id", columns={"transport_manager_id"})
 *    }
 * )
 */
class User extends AbstractUser implements OrganisationProviderInterface
{
    public const PERMISSION_ADMIN = 'admin';
    public const PERMISSION_USER = 'user';
    public const PERMISSION_TM = 'tm';

    public const USER_TYPE_INTERNAL = 'internal';
    public const USER_TYPE_ANON = 'anon';
    public const USER_TYPE_LOCAL_AUTHORITY = 'local-authority';
    public const USER_TYPE_OPERATOR = 'operator';
    public const USER_TYPE_PARTNER = 'partner';
    public const USER_TYPE_TRANSPORT_MANAGER = 'transport-manager';

    public const ERROR_ADMIN_USER_ALREADY_EXISTS = 'err_admin_user_already_exists';
    public const ERR_ANON_USERNAME = 'ERR_ANON_USERNAME';

    public const ANON_USERNAME = 'anon';

    /**
     * List of all roles available by user type
     *
     * @var array
     */
    private static $rolesAvailableByUserType = [
        self::USER_TYPE_LOCAL_AUTHORITY => [
            RoleEntity::ROLE_LOCAL_AUTHORITY_ADMIN,
            RoleEntity::ROLE_LOCAL_AUTHORITY_USER,
        ],
        self::USER_TYPE_OPERATOR => [
            RoleEntity::ROLE_OPERATOR_ADMIN,
            RoleEntity::ROLE_OPERATOR_USER,
            RoleEntity::ROLE_OPERATOR_TM,
        ],
        self::USER_TYPE_TRANSPORT_MANAGER => [
            RoleEntity::ROLE_OPERATOR_ADMIN,
            RoleEntity::ROLE_OPERATOR_USER,
            RoleEntity::ROLE_OPERATOR_TM,
        ],
        self::USER_TYPE_PARTNER => [
            RoleEntity::ROLE_PARTNER_ADMIN,
            RoleEntity::ROLE_PARTNER_USER,
        ],
        self::USER_TYPE_INTERNAL => [
            RoleEntity::ROLE_SYSTEM_ADMIN,
            RoleEntity::ROLE_INTERNAL_ADMIN,
            RoleEntity::ROLE_INTERNAL_IRHP_ADMIN,
            RoleEntity::ROLE_INTERNAL_CASE_WORKER,
            RoleEntity::ROLE_INTERNAL_READ_ONLY,
            RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY,
        ],
        self::USER_TYPE_ANON => [
            RoleEntity::ROLE_ANON
        ]
    ];

    /**
     * List of roles by user type and permission
     *
     * @var array
     */
    private static $userTypeToRoles = [
        self::USER_TYPE_LOCAL_AUTHORITY => [
            self::PERMISSION_ADMIN => [RoleEntity::ROLE_LOCAL_AUTHORITY_ADMIN],
            self::PERMISSION_USER => [RoleEntity::ROLE_LOCAL_AUTHORITY_USER],
        ],
        self::USER_TYPE_OPERATOR => [
            self::PERMISSION_ADMIN => [RoleEntity::ROLE_OPERATOR_ADMIN],
            self::PERMISSION_USER => [RoleEntity::ROLE_OPERATOR_USER],
            self::PERMISSION_TM => [RoleEntity::ROLE_OPERATOR_TM],
        ],
        self::USER_TYPE_TRANSPORT_MANAGER => [
            self::PERMISSION_ADMIN => [RoleEntity::ROLE_OPERATOR_ADMIN],
            self::PERMISSION_USER => [RoleEntity::ROLE_OPERATOR_USER],
            self::PERMISSION_TM => [RoleEntity::ROLE_OPERATOR_TM],
        ],
        self::USER_TYPE_PARTNER => [
            self::PERMISSION_ADMIN => [RoleEntity::ROLE_PARTNER_ADMIN],
            self::PERMISSION_USER => [RoleEntity::ROLE_PARTNER_USER],
        ],
    ];

    /**
     * User type
     *
     * @var string|null
     */
    protected $userType = null;

    /**
     * User constructor.
     *
     * @param string $pid      pid
     * @param string|null $userType user type
     *
     * @return void
     */
    public function __construct($pid, ?string $userType)
    {
        parent::__construct();
        $this->userType = $userType;
        $this->pid = $pid;
    }

    /**
     * Create a user
     *
     * @param string $pid      pid
     * @param string $userType user type
     * @param array  $data     Array of data as defined by Dvsa\Olcs\Transfer\Command\User\CreateUser
     *
     * @return User
     */
    public static function create($pid, $userType, $data)
    {
        $user = new self($pid, $userType);
        $user->update($data);

        return $user;
    }

    /**
     * Create anon user
     *
     * @return User
     */
    public static function anon()
    {
        $user =  new self('', self::USER_TYPE_ANON);
        $user->update(['loginId' => null, 'roles' => [RoleEntity::anon()]]);
        $user->loginId = static::ANON_USERNAME;

        return $user;
    }

    /**
     * Checks if it is an anonymous user
     *
     * @return bool
     */
    public function isAnonymous()
    {
        return (is_null($this->getLoginId()) || $this->getLoginId() === static::ANON_USERNAME);
    }

    /**
     * Is this user a System user
     *
     * @return bool
     */
    public function isSystemUser()
    {
        // If we add more System user accounts we probably want to change this to
        // return $this->getId() === \Dvsa\Olcs\Api\Rbac\IdentityProviderInterface::SYSTEM_TEAM
        return $this->getId() === IdentityProviderInterface::SYSTEM_USER;
    }

    /**
     * Update a user
     *
     * @param array $data Array of data as defined by Dvsa\Olcs\Transfer\Command\User\CreateUser
     *
     * @return User
     */
    public function update(array $data)
    {
        if (isset($data['loginId']) && $data['loginId'] === static::ANON_USERNAME) {
            throw new ValidationException(['username' => [self::ERR_ANON_USERNAME]]);
        }

        if (isset($data['loginId'])) {
            $this->loginId = $data['loginId'];
        }

        if (isset($data['userType'])) {
            $this->updateUserType($data['userType']);
        }

        if (isset($data['roles'])) {
            $this->updateRoles($data['roles']);
        }

        if (isset($data['translateToWelsh'])) {
            $this->translateToWelsh = $data['translateToWelsh'];
        }

        if (isset($data['accountDisabled'])) {
            $this->updateAccountDisabled($data['accountDisabled']);
        }

        // each type may have different update
        match ($this->getUserType()) {
            self::USER_TYPE_INTERNAL => $this->updateInternal($data),
            self::USER_TYPE_TRANSPORT_MANAGER => $this->updateTransportManager($data),
            self::USER_TYPE_PARTNER => $this->updatePartner($data),
            self::USER_TYPE_LOCAL_AUTHORITY => $this->updateLocalAuthority($data),
            self::USER_TYPE_OPERATOR => $this->updateOperator($data),
            default => $this,
        };

        return $this;
    }

    /**
     * Update user type
     *
     * @param string $userType user type
     *
     * @return User
     */
    private function updateUserType($userType)
    {
        if ($this->getUserType() !== $userType) {
            // update user type
            $this->userType = $userType;

            // reset all user type specific fields
            $this->team = null;
            $this->transportManager = null;
            $this->partnerContactDetails = null;
            $this->localAuthority = null;

            if (!in_array($userType, [self::USER_TYPE_OPERATOR, self::USER_TYPE_TRANSPORT_MANAGER])) {
                // reset org users only if switching to a user type which does not populate the value anyway
                // otherwise Operator->TM or TM->Operator fails as collection is empty
                $this->populateOrganisationUsers();
            }
        }

        return $this;
    }

    /**
     * Update roles
     *
     * @param array $roles array of roles
     *
     * @return User
     */
    private function updateRoles(array $roles)
    {
        if (!empty($roles)) {
            // check if there are any roles available for the user type
            if (empty(self::$rolesAvailableByUserType[$this->getUserType()])) {
                throw new ValidationException(['The roles selected are not available to this user type']);
            }

            $rolesAvailable = array_intersect(
                // user's roles selected
                array_map(
                    fn($role) => $role->getRole(),
                    $roles
                ),
                // list of roles available for the user type
                self::$rolesAvailableByUserType[$this->getUserType()]
            );

            // make sure that all selected roles are available to this user type
            if (count($roles) !== count($rolesAvailable)) {
                throw new ValidationException(['The roles selected are not available to this user type']);
            }
        }

        // set the new roles
        $this->roles = new ArrayCollection($roles);

        return $this;
    }

    /**
     * Update whether the account is disabled
     *
     * @param string $accountDisabled is account disabled Y or N
     *
     * @return User
     */
    private function updateAccountDisabled($accountDisabled)
    {
        $this->accountDisabled = $accountDisabled;

        if ($this->accountDisabled === 'Y') {
            // set disabled date to now
            $this->disabledDate = new \DateTime();
        } else {
            $this->disabledDate = null;
        }

        return $this;
    }

    /**
     * Update internal team
     *
     * @param array $data Array of data as defined by Dvsa\Olcs\Transfer\Command\User\CreateUser
     *
     * @return User
     */
    private function updateInternal(array $data)
    {
        if (isset($data['team'])) {
            $this->team = $data['team'];
        }

        return $this;
    }

    /**
     * Update Tm user
     *
     * @param array $data Array of data as defined by Dvsa\Olcs\Transfer\Command\User\CreateUser
     *
     * @return User
     */
    private function updateTransportManager(array $data)
    {
        if (isset($data['transportManager'])) {
            $this->transportManager = $data['transportManager'];
        }
        $this->updateOrganisationUsers($data);

        return $this;
    }

    /**
     * Update partner
     *
     * @param array $data Array of data as defined by Dvsa\Olcs\Transfer\Command\User\CreateUser
     *
     * @return User
     */
    private function updatePartner(array $data)
    {
        if (isset($data['partnerContactDetails'])) {
            $this->partnerContactDetails = $data['partnerContactDetails'];
        }

        return $this;
    }

    /**
     * Update local authority
     *
     * @param array $data Array of data as defined by Dvsa\Olcs\Transfer\Command\User\CreateUser
     *
     * @return User
     */
    private function updateLocalAuthority(array $data)
    {
        if (isset($data['localAuthority'])) {
            $this->localAuthority = $data['localAuthority'];
        }

        return $this;
    }

    /**
     * Update operator
     *
     * @param array $data Array of data as defined by Dvsa\Olcs\Transfer\Command\User\CreateUser
     *
     * @return User
     */
    private function updateOperator(array $data)
    {
        $this->updateOrganisationUsers($data);

        return $this;
    }

    /**
     * Get the user type
     *
     * @return string
     */
    public function getUserType()
    {
        if ($this->userType === null) {
            if (isset($this->team)) {
                $this->userType = self::USER_TYPE_INTERNAL;
            } elseif (isset($this->localAuthority)) {
                $this->userType = self::USER_TYPE_LOCAL_AUTHORITY;
            } elseif (isset($this->transportManager)) {
                $this->userType = self::USER_TYPE_TRANSPORT_MANAGER;
            } elseif (isset($this->partnerContactDetails)) {
                $this->userType = self::USER_TYPE_PARTNER;
            } else {
                $this->userType = self::USER_TYPE_OPERATOR;
            }
        }
        return $this->userType;
    }


    /**
     * Update organisation users
     *
     * @param array $data Array of data as defined by Dvsa\Olcs\Transfer\Command\User\CreateUser
     *
     * @return User
     */
    private function updateOrganisationUsers(array $data)
    {
        if (isset($data['organisations'])) {
            // update list of organisations
            $this->populateOrganisationUsers($data['organisations']);
        } else {
            // update isAdministrator flag only
            $orgs = array_map(
                fn($organisationUser) => $organisationUser->getOrganisation(),
                $this->getOrganisationUsers()->toArray()
            );

            $this->populateOrganisationUsers($orgs);
        }

        return $this;
    }

    /**
     * Populate organisation users
     *
     * @param array $orgs List of Dvsa\Olcs\Api\Entity\Organisation\Organisation
     *
     * @return User
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function populateOrganisationUsers(array $orgs = null)
    {
        $orgs ??= [];
        $seen = [];

        $collection = $this->getOrganisationUsers()->toArray();

        foreach ($orgs as $org) {
            $id = $org->getId();

            if (!empty($collection[$id])) {
                // update
                $collection[$id]->setIsAdministrator($this->isAdministrator() ? 'Y' : 'N');

                // mark as seen
                $seen[$id] = $id;
            } else {
                // create
                $orgUserEntity = new OrganisationUserEntity();
                $orgUserEntity->setUser($this);
                $orgUserEntity->setOrganisation($org);
                $orgUserEntity->setIsAdministrator($this->isAdministrator() ? 'Y' : 'N');

                $this->organisationUsers->add($orgUserEntity);
            }
        }

        // remove the rest
        foreach (array_diff_key($collection, $seen) as $key => $entity) {
            // unlink
            $this->organisationUsers->remove($key);
        }

        return $this;
    }

    /**
     * Checks whther the user is an administrator
     *
     * @return bool
     */
    private function isAdministrator()
    {
        // is admin if has roles for admin permission
        return $this->hasRoles(
            self::getRolesByUserType($this->getUserType(), self::PERMISSION_ADMIN)
        );
    }

    /**
     * Checks whether the user is of internal type
     *
     * @return bool
     */
    public function isInternal()
    {
        return (self::USER_TYPE_INTERNAL === $this->getUserType());
    }

    /**
     * Get permission
     *
     * @return string|null
     */
    public function getPermission()
    {
        if (empty(self::$userTypeToRoles[$this->getUserType()])) {
            return null;
        }

        foreach (self::$userTypeToRoles[$this->getUserType()] as $permission => $roles) {
            if ($this->hasRoles($roles)) {
                return $permission;
            }
        }

        return null;
    }

    /**
     * Return roles if they exist
     *
     * @param array $roles roles
     *
     * @return bool
     */
    public function hasRoles(array $roles)
    {
        return !$this->roles->isEmpty() && !empty(
            array_intersect(
                // list of roles to check for
                $roles,
                // user's roles
                array_map(
                    fn($role) => $role->getRole(),
                    $this->roles->toArray()
                )
            )
        );
    }

    /**
     * Whether the user is allowed to perform any action on given roles
     *
     * @param array $roles Roles to be validated
     *
     * @return bool
     */
    public function isAllowedToPerformActionOnRoles(array $roles)
    {
        if ($this->roles->isEmpty()) {
            // this user has no roles
            return false;
        }

        $allowedRoles = [];

        foreach ($this->roles->toArray() as $role) {
            $allowedRoles = array_merge($allowedRoles, $role->getAllowedRoles());
        }

        // all $roles must be in $allowedRoles array
        return count(array_diff($roles, $allowedRoles)) ? false : true;
    }

    /**
     * Get roles by user type
     *
     * @param string $userType   user type
     * @param string $permission permission
     *
     * @return array
     */
    public static function getRolesByUserType($userType, $permission)
    {
        if (!empty(self::$userTypeToRoles[$userType][$permission])) {
            return self::$userTypeToRoles[$userType][$permission];
        }

        return [];
    }

    /**
     * Get the related organisation for a user
     *
     * @return null|Organisation
     */
    public function getRelatedOrganisation()
    {
        if ($this->getOrganisationUsers()->isEmpty()) {
            return null;
        }
        return $this->getOrganisationUsers()->current()->getOrganisation();
    }

    /**
     * Checks if the user belongs to an org with at least one active PSV Licence
     *
     * @return bool
     */
    public function hasActivePsvLicence()
    {
        $org = $this->getRelatedOrganisation();

        if ($org !== null) {
            return $org->hasActiveLicences(LicenceEntity::LICENCE_CATEGORY_PSV);
        }

        return false;
    }

    /**
     * Returns related Organisation Name based on the user's type
     *
     * @return string
     */
    public function getRelatedOrganisationName()
    {
        $name = '';

        switch ($this->getUserType()) {
            case self::USER_TYPE_INTERNAL:
                $name = 'DVSA';
                break;
            case self::USER_TYPE_PARTNER:
                $name = $this->getPartnerContactDetails()->getDescription();
                break;
            case self::USER_TYPE_LOCAL_AUTHORITY:
                $name = $this->getLocalAuthority()->getDescription();
                break;
            default:
                $org = $this->getRelatedOrganisation();

                if ($org !== null) {
                    $name = $org->getName();
                }
                break;
        }

        return $name;
    }

    /**
     * Get calculated bundle values
     *
     * @return array
     */
    protected function getCalculatedBundleValues()
    {
        return ['userType' => $this->getUserType()];
    }

    /**
     * Get total number of vehicles
     *
     * @return int
     */
    public function getNumberOfVehicles()
    {
        $org = $this->getRelatedOrganisation();
        if ($org === null) {
            return 0;
        }
        $activeLicences = $org->getActiveLicences();
        $outstandingApplications = $org->getOutstandingApplications(true);

        $total = 0;

        /** @var LicenceEntity $licence */
        foreach ($activeLicences as $licence) {
            $total += $licence->getTotAuthVehicles();
        }

        /** @var ApplicationEntity $application */
        foreach ($outstandingApplications as $application) {
            $total += $application->getTotAuthVehicles();
        }

        return $total;
    }

    /**
     * Whether this user can be assigned a data retention record
     *
     * @return bool
     */
    public function canBeAssignedDataRetention()
    {
        return $this->isInternal() && !$this->isDisabled();
    }

    /**
     * Whether this user is disabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->accountDisabled === 'Y';
    }

    /**
     * Is eligible for permits
     *
     * @return bool
     */
    public function isEligibleForPermits()
    {
        $eligibleForPermits = false;

        $org = $this->getRelatedOrganisation();

        if ($org instanceof Organisation) {
            $eligibleForPermits = $org->isEligibleForPermits();
        }

        return $eligibleForPermits;
    }

    /**
     * User's attached organisation has never had a submitted a licence application.
     *
     * @return bool
     */
    public function hasOrganisationSubmittedLicenceApplication(): bool
    {
        $org = $this->getRelatedOrganisation();
        if ($org instanceof Organisation) {
            return $org->hasSubmittedLicenceApplication();
        }

        return false;
    }

    /**
     * Whether the user is allowed to reset their password
     *
     * @return bool
     */
    public function canResetPassword(): bool
    {
        //disabled users can't reset
        return !$this->isDisabled();
    }
}
