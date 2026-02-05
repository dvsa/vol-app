<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractUser Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="user",
 *    indexes={
 *        @ORM\Index(name="ix_user_contact_details_id", columns={"contact_details_id"}),
 *        @ORM\Index(name="ix_user_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_user_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_user_local_authority_id", columns={"local_authority_id"}),
 *        @ORM\Index(name="ix_user_partner_contact_details_id", columns={"partner_contact_details_id"}),
 *        @ORM\Index(name="ix_user_team_id", columns={"team_id"}),
 *        @ORM\Index(name="ix_user_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="uk_user_login_id", columns={"login_id"}),
 *        @ORM\Index(name="uk_user_pid", columns={"pid"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_user_login_id", columns={"login_id"}),
 *        @ORM\UniqueConstraint(name="uk_user_pid", columns={"pid"})
 *    }
 * )
 */
abstract class AbstractUser implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Foreign Key to team
     *
     * @var \Dvsa\Olcs\Api\Entity\User\Team
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\Team", fetch="LAZY")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=true)
     */
    protected $team;

    /**
     * If user is also a transport manager.
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id", nullable=true)
     */
    protected $transportManager;

    /**
     * If user is a member of a local authority a link to the LA details.
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\LocalAuthority", fetch="LAZY")
     * @ORM\JoinColumn(name="local_authority_id", referencedColumnName="id", nullable=true)
     */
    protected $localAuthority;

    /**
     * Foreign Key to contact_details
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_details_id", referencedColumnName="id", nullable=true)
     */
    protected $contactDetails;

    /**
     * If user is part of a partner, such as HMRC a link to the partners details.
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="partner_contact_details_id", referencedColumnName="id", nullable=true)
     */
    protected $partnerContactDetails;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    protected $lastModifiedBy;

    /**
     * Pid
     *
     * @var string
     *
     * @ORM\Column(type="string", name="pid", length=255, nullable=true)
     */
    protected $pid;

    /**
     * Login id
     *
     * @var string
     *
     * @ORM\Column(type="string", name="login_id", length=40, nullable=true)
     */
    protected $loginId;

    /**
     * Account locked by DVSA. Cannot be unlocked by non DVSA user.
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="account_disabled", nullable=false, options={"default": 0})
     */
    protected $accountDisabled = 0;

    /**
     * Date when the account was disabled
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="disabled_date", nullable=true)
     */
    protected $disabledDate;

    /**
     * Communication to be in Welsh
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="translate_to_welsh", nullable=false, options={"default": 0})
     */
    protected $translateToWelsh = 0;

    /**
     * Last login at
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_login_at", nullable=true)
     */
    protected $lastLoginAt;

    /**
     * Whether user has agreed to terms and conditions
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="terms_agreed", nullable=false, options={"default": 0})
     */
    protected $termsAgreed = 0;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Roles
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\User\Role", inversedBy="users", fetch="LAZY")
     * @ORM\JoinTable(name="user_role",
     *     joinColumns={
     *         @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $roles;

    /**
     * OrganisationUsers
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser", mappedBy="user", cascade={"persist"}, indexBy="organisation_id", orphanRemoval=true)
     */
    protected $organisationUsers;

    /**
     * PasswordResets
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\User\UserPasswordReset", mappedBy="user", fetch="EXTRA_LAZY")
     */
    protected $passwordResets;

    /**
     * Initialise the collections
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise collections
     */
    public function initCollections(): void
    {
        $this->roles = new ArrayCollection();
        $this->organisationUsers = new ArrayCollection();
        $this->passwordResets = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the team
     *
     * @param \Dvsa\Olcs\Api\Entity\User\Team $team new value being set
     *
     * @return User
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get the team
     *
     * @return \Dvsa\Olcs\Api\Entity\User\Team     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager new value being set
     *
     * @return User
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;

        return $this;
    }

    /**
     * Get the transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the local authority
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority $localAuthority new value being set
     *
     * @return User
     */
    public function setLocalAuthority($localAuthority)
    {
        $this->localAuthority = $localAuthority;

        return $this;
    }

    /**
     * Get the local authority
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority     */
    public function getLocalAuthority()
    {
        return $this->localAuthority;
    }

    /**
     * Set the contact details
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $contactDetails new value being set
     *
     * @return User
     */
    public function setContactDetails($contactDetails)
    {
        $this->contactDetails = $contactDetails;

        return $this;
    }

    /**
     * Get the contact details
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    /**
     * Set the partner contact details
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $partnerContactDetails new value being set
     *
     * @return User
     */
    public function setPartnerContactDetails($partnerContactDetails)
    {
        $this->partnerContactDetails = $partnerContactDetails;

        return $this;
    }

    /**
     * Get the partner contact details
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails     */
    public function getPartnerContactDetails()
    {
        return $this->partnerContactDetails;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return User
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return User
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the pid
     *
     * @param string $pid new value being set
     *
     * @return User
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Get the pid
     *
     * @return string     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set the login id
     *
     * @param string $loginId new value being set
     *
     * @return User
     */
    public function setLoginId($loginId)
    {
        $this->loginId = $loginId;

        return $this;
    }

    /**
     * Get the login id
     *
     * @return string     */
    public function getLoginId()
    {
        return $this->loginId;
    }

    /**
     * Set the account disabled
     *
     * @param string $accountDisabled new value being set
     *
     * @return User
     */
    public function setAccountDisabled($accountDisabled)
    {
        $this->accountDisabled = $accountDisabled;

        return $this;
    }

    /**
     * Get the account disabled
     *
     * @return string     */
    public function getAccountDisabled()
    {
        return $this->accountDisabled;
    }

    /**
     * Set the disabled date
     *
     * @param \DateTime $disabledDate new value being set
     *
     * @return User
     */
    public function setDisabledDate($disabledDate)
    {
        $this->disabledDate = $disabledDate;

        return $this;
    }

    /**
     * Get the disabled date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDisabledDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->disabledDate);
        }

        return $this->disabledDate;
    }

    /**
     * Set the translate to welsh
     *
     * @param string $translateToWelsh new value being set
     *
     * @return User
     */
    public function setTranslateToWelsh($translateToWelsh)
    {
        $this->translateToWelsh = $translateToWelsh;

        return $this;
    }

    /**
     * Get the translate to welsh
     *
     * @return string     */
    public function getTranslateToWelsh()
    {
        return $this->translateToWelsh;
    }

    /**
     * Set the last login at
     *
     * @param \DateTime $lastLoginAt new value being set
     *
     * @return User
     */
    public function setLastLoginAt($lastLoginAt)
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    /**
     * Get the last login at
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getLastLoginAt($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastLoginAt);
        }

        return $this->lastLoginAt;
    }

    /**
     * Set the terms agreed
     *
     * @param bool $termsAgreed new value being set
     *
     * @return User
     */
    public function setTermsAgreed($termsAgreed)
    {
        $this->termsAgreed = $termsAgreed;

        return $this;
    }

    /**
     * Get the terms agreed
     *
     * @return bool     */
    public function getTermsAgreed()
    {
        return $this->termsAgreed;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return User
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the roles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $roles collection being set as the value
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get the roles
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add a roles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $roles collection being added
     *
     * @return User
     */
    public function addRoles($roles)
    {
        if ($roles instanceof ArrayCollection) {
            $this->roles = new ArrayCollection(
                array_merge(
                    $this->roles->toArray(),
                    $roles->toArray()
                )
            );
        } elseif (!$this->roles->contains($roles)) {
            $this->roles->add($roles);
        }

        return $this;
    }

    /**
     * Remove a roles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $roles collection being removed
     *
     * @return User
     */
    public function removeRoles($roles)
    {
        if ($this->roles->contains($roles)) {
            $this->roles->removeElement($roles);
        }

        return $this;
    }

    /**
     * Set the organisation users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $organisationUsers collection being set as the value
     *
     * @return User
     */
    public function setOrganisationUsers($organisationUsers)
    {
        $this->organisationUsers = $organisationUsers;

        return $this;
    }

    /**
     * Get the organisation users
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOrganisationUsers()
    {
        return $this->organisationUsers;
    }

    /**
     * Add a organisation users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $organisationUsers collection being added
     *
     * @return User
     */
    public function addOrganisationUsers($organisationUsers)
    {
        if ($organisationUsers instanceof ArrayCollection) {
            $this->organisationUsers = new ArrayCollection(
                array_merge(
                    $this->organisationUsers->toArray(),
                    $organisationUsers->toArray()
                )
            );
        } elseif (!$this->organisationUsers->contains($organisationUsers)) {
            $this->organisationUsers->add($organisationUsers);
        }

        return $this;
    }

    /**
     * Remove a organisation users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $organisationUsers collection being removed
     *
     * @return User
     */
    public function removeOrganisationUsers($organisationUsers)
    {
        if ($this->organisationUsers->contains($organisationUsers)) {
            $this->organisationUsers->removeElement($organisationUsers);
        }

        return $this;
    }

    /**
     * Set the password resets
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $passwordResets collection being set as the value
     *
     * @return User
     */
    public function setPasswordResets($passwordResets)
    {
        $this->passwordResets = $passwordResets;

        return $this;
    }

    /**
     * Get the password resets
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPasswordResets()
    {
        return $this->passwordResets;
    }

    /**
     * Add a password resets
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $passwordResets collection being added
     *
     * @return User
     */
    public function addPasswordResets($passwordResets)
    {
        if ($passwordResets instanceof ArrayCollection) {
            $this->passwordResets = new ArrayCollection(
                array_merge(
                    $this->passwordResets->toArray(),
                    $passwordResets->toArray()
                )
            );
        } elseif (!$this->passwordResets->contains($passwordResets)) {
            $this->passwordResets->add($passwordResets);
        }

        return $this;
    }

    /**
     * Remove a password resets
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $passwordResets collection being removed
     *
     * @return User
     */
    public function removePasswordResets($passwordResets)
    {
        if ($this->passwordResets->contains($passwordResets)) {
            $this->passwordResets->removeElement($passwordResets);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
