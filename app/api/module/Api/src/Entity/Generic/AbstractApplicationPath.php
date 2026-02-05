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
 * AbstractApplicationPath Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="application_path",
 *    indexes={
 *        @ORM\Index(name="fk_application_path_application_path_group_id", columns={"application_path_group_id"}),
 *        @ORM\Index(name="fk_application_path_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_application_path_irhp_permit_type_id_irhp_permit_type_id", columns={"irhp_permit_type_id"}),
 *        @ORM\Index(name="fk_application_path_last_modified_by_user_id", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractApplicationPath implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * IrhpPermitType
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_permit_type_id", referencedColumnName="id")
     */
    protected $irhpPermitType;

    /**
     * ApplicationPathGroup
     *
     * @var \Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup", fetch="LAZY")
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
     * Effective from
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="effective_from", nullable=true)
     */
    protected $effectiveFrom;

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
     * ApplicationSteps
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationStep", mappedBy="applicationPath")
     * @ORM\OrderBy({"weight" = "ASC"})
     */
    protected $applicationSteps;

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
        $this->applicationSteps = new ArrayCollection();
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
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the irhp permit type
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType $irhpPermitType new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType     */
    public function getIrhpPermitType()
    {
        return $this->irhpPermitType;
    }

    /**
     * Set the application path group
     *
     * @param \Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup $applicationPathGroup new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup     */
    public function getApplicationPathGroup()
    {
        return $this->applicationPathGroup;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
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
     * @return string     */
    public function getTitle()
    {
        return $this->title;
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
     * @return \DateTime     */
    public function getEffectiveFrom($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveFrom);
        }

        return $this->effectiveFrom;
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
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the application steps
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applicationSteps collection being set as the value
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getApplicationSteps()
    {
        return $this->applicationSteps;
    }

    /**
     * Add a application steps
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $applicationSteps collection being added
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

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
