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
 * AbstractSiPenalty Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="si_penalty",
 *    indexes={
 *        @ORM\Index(name="fk_si_penalty_si_penalty_requested_id_si_penalty_requested_id", columns={"si_penalty_erru_requested_id"}),
 *        @ORM\Index(name="ix_si_penalty_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_penalty_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_si_penalty_serious_infringement_id", columns={"serious_infringement_id"}),
 *        @ORM\Index(name="ix_si_penalty_si_penalty_type_id", columns={"si_penalty_type_id"})
 *    }
 * )
 */
abstract class AbstractSiPenalty implements BundleSerializableInterface, JsonSerializable
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
     * SiPenaltyErruRequested
     *
     * @var \Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested", fetch="LAZY")
     * @ORM\JoinColumn(name="si_penalty_erru_requested_id", referencedColumnName="id", nullable=true)
     */
    protected $siPenaltyErruRequested;

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
     * Foreign Key to si_penalty_type
     *
     * @var \Dvsa\Olcs\Api\Entity\Si\SiPenaltyType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Si\SiPenaltyType", fetch="LAZY")
     * @ORM\JoinColumn(name="si_penalty_type_id", referencedColumnName="id")
     */
    protected $siPenaltyType;

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
     * imposed
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="imposed", nullable=true)
     */
    protected $imposed;

    /**
     * Start date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="start_date", nullable=true)
     */
    protected $startDate;

    /**
     * End date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="end_date", nullable=true)
     */
    protected $endDate;

    /**
     * Reason not imposed
     *
     * @var string
     *
     * @ORM\Column(type="string", name="reason_not_imposed", length=500, nullable=true)
     */
    protected $reasonNotImposed;

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
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return SiPenalty
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
     * Set the si penalty erru requested
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested $siPenaltyErruRequested new value being set
     *
     * @return SiPenalty
     */
    public function setSiPenaltyErruRequested($siPenaltyErruRequested)
    {
        $this->siPenaltyErruRequested = $siPenaltyErruRequested;

        return $this;
    }

    /**
     * Get the si penalty erru requested
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested     */
    public function getSiPenaltyErruRequested()
    {
        return $this->siPenaltyErruRequested;
    }

    /**
     * Set the serious infringement
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\SeriousInfringement $seriousInfringement new value being set
     *
     * @return SiPenalty
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
     * Set the si penalty type
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\SiPenaltyType $siPenaltyType new value being set
     *
     * @return SiPenalty
     */
    public function setSiPenaltyType($siPenaltyType)
    {
        $this->siPenaltyType = $siPenaltyType;

        return $this;
    }

    /**
     * Get the si penalty type
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\SiPenaltyType     */
    public function getSiPenaltyType()
    {
        return $this->siPenaltyType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return SiPenalty
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
     * @return SiPenalty
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
     * Set the imposed
     *
     * @param string $imposed new value being set
     *
     * @return SiPenalty
     */
    public function setImposed($imposed)
    {
        $this->imposed = $imposed;

        return $this;
    }

    /**
     * Get the imposed
     *
     * @return string     */
    public function getImposed()
    {
        return $this->imposed;
    }

    /**
     * Set the start date
     *
     * @param \DateTime $startDate new value being set
     *
     * @return SiPenalty
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get the start date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getStartDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->startDate);
        }

        return $this->startDate;
    }

    /**
     * Set the end date
     *
     * @param \DateTime $endDate new value being set
     *
     * @return SiPenalty
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get the end date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getEndDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->endDate);
        }

        return $this->endDate;
    }

    /**
     * Set the reason not imposed
     *
     * @param string $reasonNotImposed new value being set
     *
     * @return SiPenalty
     */
    public function setReasonNotImposed($reasonNotImposed)
    {
        $this->reasonNotImposed = $reasonNotImposed;

        return $this;
    }

    /**
     * Get the reason not imposed
     *
     * @return string     */
    public function getReasonNotImposed()
    {
        return $this->reasonNotImposed;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SiPenalty
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
     * @return SiPenalty
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
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}