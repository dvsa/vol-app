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
 * AbstractSiPenaltyErruImposed Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="si_penalty_erru_imposed",
 *    indexes={
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_executed", columns={"executed"}),
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_serious_infringement_id", columns={"serious_infringement_id"}),
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_si_penalty_imposed_type_id", columns={"si_penalty_imposed_type_id"})
 *    }
 * )
 */
abstract class AbstractSiPenaltyErruImposed implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to si_penalty_imposed_type
     *
     * @var \Dvsa\Olcs\Api\Entity\Si\SiPenaltyImposedType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Si\SiPenaltyImposedType", fetch="LAZY")
     * @ORM\JoinColumn(name="si_penalty_imposed_type_id", referencedColumnName="id")
     */
    protected $siPenaltyImposedType;

    /**
     * Executed
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="executed", referencedColumnName="id", nullable=true)
     */
    protected $executed;

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
     * Penalty imposed identifier
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="penalty_imposed_identifier", nullable=true)
     */
    protected $penaltyImposedIdentifier;

    /**
     * Final decision date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="final_decision_date", nullable=true)
     */
    protected $finalDecisionDate;

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
     * @return SiPenaltyErruImposed
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
     * @return SiPenaltyErruImposed
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
     * Set the si penalty imposed type
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\SiPenaltyImposedType $siPenaltyImposedType new value being set
     *
     * @return SiPenaltyErruImposed
     */
    public function setSiPenaltyImposedType($siPenaltyImposedType)
    {
        $this->siPenaltyImposedType = $siPenaltyImposedType;

        return $this;
    }

    /**
     * Get the si penalty imposed type
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\SiPenaltyImposedType     */
    public function getSiPenaltyImposedType()
    {
        return $this->siPenaltyImposedType;
    }

    /**
     * Set the executed
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $executed new value being set
     *
     * @return SiPenaltyErruImposed
     */
    public function setExecuted($executed)
    {
        $this->executed = $executed;

        return $this;
    }

    /**
     * Get the executed
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getExecuted()
    {
        return $this->executed;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return SiPenaltyErruImposed
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
     * @return SiPenaltyErruImposed
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
     * Set the penalty imposed identifier
     *
     * @param int $penaltyImposedIdentifier new value being set
     *
     * @return SiPenaltyErruImposed
     */
    public function setPenaltyImposedIdentifier($penaltyImposedIdentifier)
    {
        $this->penaltyImposedIdentifier = $penaltyImposedIdentifier;

        return $this;
    }

    /**
     * Get the penalty imposed identifier
     *
     * @return int     */
    public function getPenaltyImposedIdentifier()
    {
        return $this->penaltyImposedIdentifier;
    }

    /**
     * Set the final decision date
     *
     * @param \DateTime $finalDecisionDate new value being set
     *
     * @return SiPenaltyErruImposed
     */
    public function setFinalDecisionDate($finalDecisionDate)
    {
        $this->finalDecisionDate = $finalDecisionDate;

        return $this;
    }

    /**
     * Get the final decision date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getFinalDecisionDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->finalDecisionDate);
        }

        return $this->finalDecisionDate;
    }

    /**
     * Set the start date
     *
     * @param \DateTime $startDate new value being set
     *
     * @return SiPenaltyErruImposed
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
     * @return SiPenaltyErruImposed
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
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SiPenaltyErruImposed
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
     * @return SiPenaltyErruImposed
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