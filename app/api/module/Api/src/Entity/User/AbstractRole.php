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
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractRole Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="role",
 *    indexes={
 *        @ORM\Index(name="ix_role_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_role_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractRole implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Short name of role
     *
     * @var string
     *
     * @ORM\Column(type="string", name="role", length=100, nullable=false)
     */
    protected $role = '';

    /**
     * Description of role.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     */
    protected $description;

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
     * Users
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\User\User", mappedBy="roles", fetch="LAZY")
     */
    protected $users;

    /**
     * RolePermissions
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\User\RolePermission", mappedBy="role")
     */
    protected $rolePermissions;

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
        $this->users = new ArrayCollection();
        $this->rolePermissions = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Role
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Role
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
     * @return Role
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
     * Set the role
     *
     * @param string $role new value being set
     *
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the role
     *
     * @return string     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return Role
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Role
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
     * Set the users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $users collection being set as the value
     *
     * @return Role
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get the users
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add a users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $users collection being added
     *
     * @return Role
     */
    public function addUsers($users)
    {
        if ($users instanceof ArrayCollection) {
            $this->users = new ArrayCollection(
                array_merge(
                    $this->users->toArray(),
                    $users->toArray()
                )
            );
        } elseif (!$this->users->contains($users)) {
            $this->users->add($users);
        }

        return $this;
    }

    /**
     * Remove a users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $users collection being removed
     *
     * @return Role
     */
    public function removeUsers($users)
    {
        if ($this->users->contains($users)) {
            $this->users->removeElement($users);
        }

        return $this;
    }

    /**
     * Set the role permissions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $rolePermissions collection being set as the value
     *
     * @return Role
     */
    public function setRolePermissions($rolePermissions)
    {
        $this->rolePermissions = $rolePermissions;

        return $this;
    }

    /**
     * Get the role permissions
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRolePermissions()
    {
        return $this->rolePermissions;
    }

    /**
     * Add a role permissions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $rolePermissions collection being added
     *
     * @return Role
     */
    public function addRolePermissions($rolePermissions)
    {
        if ($rolePermissions instanceof ArrayCollection) {
            $this->rolePermissions = new ArrayCollection(
                array_merge(
                    $this->rolePermissions->toArray(),
                    $rolePermissions->toArray()
                )
            );
        } elseif (!$this->rolePermissions->contains($rolePermissions)) {
            $this->rolePermissions->add($rolePermissions);
        }

        return $this;
    }

    /**
     * Remove a role permissions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $rolePermissions collection being removed
     *
     * @return Role
     */
    public function removeRolePermissions($rolePermissions)
    {
        if ($this->rolePermissions->contains($rolePermissions)) {
            $this->rolePermissions->removeElement($rolePermissions);
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
