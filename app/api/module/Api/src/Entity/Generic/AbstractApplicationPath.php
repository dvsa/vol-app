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
 * ApplicationPath Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="application_path",
 *    indexes={
 *        @ORM\Index(name="fk_application_path_application_path_group_id",
     *     columns={"application_path_group_id"}),
 *        @ORM\Index(name="fk_application_path_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_application_path_irhp_permit_type_id_irhp_permit_type_id",
     *     columns={"irhp_permit_type_id"}),
 *        @ORM\Index(name="fk_application_path_last_modified_by_user_id",
     *     columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractApplicationPath implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Application path group
     *
     * @var \Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup",
     *     fetch="LAZY",
     *     inversedBy="applicationPaths"
     * )
     * @ORM\JoinColumn(name="application_path_group_id", referencedColumnName="id", nullable=true)
     */
    protected $applicationPathGroup;

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
     * Effective from
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="effective_from", nullable=true)
     */
    protected $effectiveFrom;

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
     * Irhp permit type
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType",
     *     fetch="LAZY",
     *     inversedBy="applicationPaths"
     * )
     * @ORM\JoinColumn(name="irhp_permit_type_id", referencedColumnName="id", nullable=false)
     */
    protected $irhpPermitType;

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
     * Title
     *
     * @var string
     *
     * @ORM\Column(type="string", name="title", length=100, nullable=true)
     */
    protected $title;

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
     * Application step
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationStep",
     *     mappedBy="applicationPath"
     * )
     * @ORM\OrderBy({"weight" = "ASC"})
     */
    protected $applicationSteps;

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
        $this->applicationSteps = new ArrayCollection();
    }

    /**
     * Set the application path group
     *
     * @param \Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup $applicationPathGroup entity being set as the value
     *
     * @return ApplicationPath
     */
    public function setApplicationPathGroup($applicationPathGroup)
    {
        $this->applicationPathGroup = $applicationPathGroup;

        return $this;
    }

    /**
     * Get the application path group
     *
     * @return \Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup
     */
    public function getApplicationPathGroup()
    {
        return $this->applicationPathGroup;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return ApplicationPath
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
     * Set the effective from
     *
     * @param \DateTime $effectiveFrom new value being set
     *
     * @return ApplicationPath
     */
    public function setEffectiveFrom($effectiveFrom)
    {
        $this->effectiveFrom = $effectiveFrom;

        return $this;
    }

    /**
     * Get the effective from
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getEffectiveFrom($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveFrom);
        }

        return $this->effectiveFrom;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return ApplicationPath
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
     * Set the irhp permit type
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType $irhpPermitType entity being set as the value
     *
     * @return ApplicationPath
     */
    public function setIrhpPermitType($irhpPermitType)
    {
        $this->irhpPermitType = $irhpPermitType;

        return $this;
    }

    /**
     * Get the irhp permit type
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType
     */
    public function getIrhpPermitType()
    {
        return $this->irhpPermitType;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return ApplicationPath
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
     * Set the title
     *
     * @param string $title new value being set
     *
     * @return ApplicationPath
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ApplicationPath
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
     * Set the application step
     *
     * @param ArrayCollection $applicationSteps collection being set as the value
     *
     * @return ApplicationPath
     */
    public function setApplicationSteps($applicationSteps)
    {
        $this->applicationSteps = $applicationSteps;

        return $this;
    }

    /**
     * Get the application steps
     *
     * @return ArrayCollection
     */
    public function getApplicationSteps()
    {
        return $this->applicationSteps;
    }

    /**
     * Add a application steps
     *
     * @param ArrayCollection|mixed $applicationSteps collection being added
     *
     * @return ApplicationPath
     */
    public function addApplicationSteps($applicationSteps)
    {
        if ($applicationSteps instanceof ArrayCollection) {
            $this->applicationSteps = new ArrayCollection(
                array_merge(
                    $this->applicationSteps->toArray(),
                    $applicationSteps->toArray()
                )
            );
        } elseif (!$this->applicationSteps->contains($applicationSteps)) {
            $this->applicationSteps->add($applicationSteps);
        }

        return $this;
    }

    /**
     * Remove a application steps
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applicationSteps collection being removed
     *
     * @return ApplicationPath
     */
    public function removeApplicationSteps($applicationSteps)
    {
        if ($this->applicationSteps->contains($applicationSteps)) {
            $this->applicationSteps->removeElement($applicationSteps);
        }

        return $this;
    }
}
