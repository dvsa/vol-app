<?php

declare(strict_types=1);

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
 * AbstractApplicationPathGroup Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
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
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=255, nullable=false)
     */
    protected $name = '';

    /**
     * Is visible in internal
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_visible_in_internal", nullable=false, options={"default": 1})
     */
    protected $isVisibleInInternal = 1;

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
     * ApplicationPaths
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationPath", mappedBy="applicationPathGroup")
     */
    protected $applicationPaths;

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
        $this->applicationPaths = new ArrayCollection();
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
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
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
     * @return string     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the is visible in internal
     *
     * @param bool $isVisibleInInternal new value being set
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
     * @return bool     */
    public function getIsVisibleInInternal()
    {
        return $this->isVisibleInInternal;
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
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the application paths
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applicationPaths collection being set as the value
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getApplicationPaths()
    {
        return $this->applicationPaths;
    }

    /**
     * Add a application paths
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $applicationPaths collection being added
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

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}