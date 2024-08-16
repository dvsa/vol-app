<?php

namespace Dvsa\Olcs\Api\Entity\Generic;

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
 * ApplicationPathGroup Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="application_path_group",
 *    indexes={
 *        @ORM\Index(name="ix_application_path_group_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_application_path_group_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractApplicationPathGroup implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Is visible in internal
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean",
     *     name="is_visible_in_internal",
     *     nullable=false,
     *     options={"default": 1})
     */
    protected $isVisibleInInternal = 1;

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
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=255, nullable=false)
     */
    protected $name;

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
     * Application path
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationPath",
     *     mappedBy="applicationPathGroup"
     * )
     */
    protected $applicationPaths;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->applicationPaths = new ArrayCollection();
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return ApplicationPathGroup
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return ApplicationPathGroup
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the is visible in internal
     *
     * @param boolean $isVisibleInInternal new value being set
     *
     * @return ApplicationPathGroup
     */
    public function setIsVisibleInInternal($isVisibleInInternal)
    {
        $this->isVisibleInInternal = $isVisibleInInternal;

        return $this;
    }

    /**
     * Get the is visible in internal
     *
     * @return boolean
     */
    public function getIsVisibleInInternal()
    {
        return $this->isVisibleInInternal;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return ApplicationPathGroup
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return ApplicationPathGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ApplicationPathGroup
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the application path
     *
     * @param ArrayCollection $applicationPaths collection being set as the value
     *
     * @return ApplicationPathGroup
     */
    public function setApplicationPaths($applicationPaths)
    {
        $this->applicationPaths = $applicationPaths;

        return $this;
    }

    /**
     * Get the application paths
     *
     * @return ArrayCollection
     */
    public function getApplicationPaths()
    {
        return $this->applicationPaths;
    }

    /**
     * Add a application paths
     *
     * @param ArrayCollection|mixed $applicationPaths collection being added
     *
     * @return ApplicationPathGroup
     */
    public function addApplicationPaths($applicationPaths)
    {
        if ($applicationPaths instanceof ArrayCollection) {
            $this->applicationPaths = new ArrayCollection(
                array_merge(
                    $this->applicationPaths->toArray(),
                    $applicationPaths->toArray()
                )
            );
        } elseif (!$this->applicationPaths->contains($applicationPaths)) {
            $this->applicationPaths->add($applicationPaths);
        }

        return $this;
    }

    /**
     * Remove a application paths
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applicationPaths collection being removed
     *
     * @return ApplicationPathGroup
     */
    public function removeApplicationPaths($applicationPaths)
    {
        if ($this->applicationPaths->contains($applicationPaths)) {
            $this->applicationPaths->removeElement($applicationPaths);
        }

        return $this;
    }
}
