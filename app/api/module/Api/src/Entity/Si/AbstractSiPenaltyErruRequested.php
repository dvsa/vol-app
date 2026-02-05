<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Si;

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
 * AbstractSiPenaltyErruRequested Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="si_penalty_erru_requested",
 *    indexes={
 *        @ORM\Index(name="ix_si_penalty_erru_requested_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_penalty_erru_requested_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_si_penalty_erru_requested_serious_infringement_id", columns={"serious_infringement_id"}),
 *        @ORM\Index(name="ix_si_penalty_erru_requested_si_penalty_requested_type_id", columns={"si_penalty_requested_type_id"})
 *    }
 * )
 */
abstract class AbstractSiPenaltyErruRequested implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to serious_infringement
     *
     * @var \Dvsa\Olcs\Api\Entity\Si\SeriousInfringement
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Si\SeriousInfringement", fetch="LAZY")
     * @ORM\JoinColumn(name="serious_infringement_id", referencedColumnName="id")
     */
    protected $seriousInfringement;

    /**
     * Foreign Key to si_penalty_requested_type
     *
     * @var \Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType", fetch="LAZY")
     * @ORM\JoinColumn(name="si_penalty_requested_type_id", referencedColumnName="id")
     */
    protected $siPenaltyRequestedType;

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
     * Penalty requested identifier
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="penalty_requested_identifier", nullable=true)
     */
    protected $penaltyRequestedIdentifier;

    /**
     * Number of months.
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="duration", nullable=true)
     */
    protected $duration;

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
     * Used to map FKs during ETL. Can be dropped safely when OLBS decommissioned
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * AppliedPenalties
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Si\SiPenalty", mappedBy="siPenaltyErruRequested")
     */
    protected $appliedPenalties;

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
        $this->appliedPenalties = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return SiPenaltyErruRequested
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
     * Set the serious infringement
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\SeriousInfringement $seriousInfringement new value being set
     *
     * @return SiPenaltyErruRequested
     */
    public function setSeriousInfringement($seriousInfringement)
    {
        $this->seriousInfringement = $seriousInfringement;

        return $this;
    }

    /**
     * Get the serious infringement
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\SeriousInfringement     */
    public function getSeriousInfringement()
    {
        return $this->seriousInfringement;
    }

    /**
     * Set the si penalty requested type
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType $siPenaltyRequestedType new value being set
     *
     * @return SiPenaltyErruRequested
     */
    public function setSiPenaltyRequestedType($siPenaltyRequestedType)
    {
        $this->siPenaltyRequestedType = $siPenaltyRequestedType;

        return $this;
    }

    /**
     * Get the si penalty requested type
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType     */
    public function getSiPenaltyRequestedType()
    {
        return $this->siPenaltyRequestedType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return SiPenaltyErruRequested
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
     * @return SiPenaltyErruRequested
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
     * Set the penalty requested identifier
     *
     * @param int $penaltyRequestedIdentifier new value being set
     *
     * @return SiPenaltyErruRequested
     */
    public function setPenaltyRequestedIdentifier($penaltyRequestedIdentifier)
    {
        $this->penaltyRequestedIdentifier = $penaltyRequestedIdentifier;

        return $this;
    }

    /**
     * Get the penalty requested identifier
     *
     * @return int     */
    public function getPenaltyRequestedIdentifier()
    {
        return $this->penaltyRequestedIdentifier;
    }

    /**
     * Set the duration
     *
     * @param int $duration new value being set
     *
     * @return SiPenaltyErruRequested
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get the duration
     *
     * @return int     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SiPenaltyErruRequested
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
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return SiPenaltyErruRequested
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the applied penalties
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $appliedPenalties collection being set as the value
     *
     * @return SiPenaltyErruRequested
     */
    public function setAppliedPenalties($appliedPenalties)
    {
        $this->appliedPenalties = $appliedPenalties;

        return $this;
    }

    /**
     * Get the applied penalties
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAppliedPenalties()
    {
        return $this->appliedPenalties;
    }

    /**
     * Add a applied penalties
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $appliedPenalties collection being added
     *
     * @return SiPenaltyErruRequested
     */
    public function addAppliedPenalties($appliedPenalties)
    {
        if ($appliedPenalties instanceof ArrayCollection) {
            $this->appliedPenalties = new ArrayCollection(
                array_merge(
                    $this->appliedPenalties->toArray(),
                    $appliedPenalties->toArray()
                )
            );
        } elseif (!$this->appliedPenalties->contains($appliedPenalties)) {
            $this->appliedPenalties->add($appliedPenalties);
        }

        return $this;
    }

    /**
     * Remove a applied penalties
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $appliedPenalties collection being removed
     *
     * @return SiPenaltyErruRequested
     */
    public function removeAppliedPenalties($appliedPenalties)
    {
        if ($this->appliedPenalties->contains($appliedPenalties)) {
            $this->appliedPenalties->removeElement($appliedPenalties);
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
