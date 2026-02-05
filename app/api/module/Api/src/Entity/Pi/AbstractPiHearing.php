<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Pi;

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
 * AbstractPiHearing Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="pi_hearing",
 *    indexes={
 *        @ORM\Index(name="ix_pi_hearing_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_pi_hearing_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_pi_hearing_pi_id", columns={"pi_id"}),
 *        @ORM\Index(name="ix_pi_hearing_presided_by_role", columns={"presided_by_role"}),
 *        @ORM\Index(name="ix_pi_hearing_presiding_tc_id", columns={"presiding_tc_id"}),
 *        @ORM\Index(name="ix_pi_hearing_venue_id", columns={"venue_id"}),
 *        @ORM\Index(name="uk_pi_hearing_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_pi_hearing_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"})
 *    }
 * )
 */
abstract class AbstractPiHearing implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to pi
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\Pi
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\Pi", fetch="LAZY")
     * @ORM\JoinColumn(name="pi_id", referencedColumnName="id")
     */
    protected $pi;

    /**
     * Foreign Key to presiding_tc
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\PresidingTc
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\PresidingTc", fetch="LAZY")
     * @ORM\JoinColumn(name="presiding_tc_id", referencedColumnName="id", nullable=true)
     */
    protected $presidingTc;

    /**
     * PresidedByRole
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="presided_by_role", referencedColumnName="id", nullable=true)
     */
    protected $presidedByRole;

    /**
     * The venue at the time of selection is stored in venue_other. If venue data changes, other still stores data at time of selection.
     *
     * @var \Dvsa\Olcs\Api\Entity\Venue
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Venue", fetch="LAZY")
     * @ORM\JoinColumn(name="venue_id", referencedColumnName="id", nullable=true)
     */
    protected $venue;

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
     * Hearing date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="hearing_date", nullable=true)
     */
    protected $hearingDate;

    /**
     * Half day / Full day
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_full_day", nullable=true)
     */
    protected $isFullDay;

    /**
     * Presiding tc other
     *
     * @var string
     *
     * @ORM\Column(type="string", name="presiding_tc_other", length=45, nullable=true)
     */
    protected $presidingTcOther;

    /**
     * Venue other
     *
     * @var string
     *
     * @ORM\Column(type="string", name="venue_other", length=255, nullable=true)
     */
    protected $venueOther;

    /**
     * Witnesses
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="witnesses", nullable=true)
     */
    protected $witnesses;

    /**
     * Drivers
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="drivers", nullable=true)
     */
    protected $drivers;

    /**
     * isCancelled
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_cancelled", nullable=false, options={"default": 0})
     */
    protected $isCancelled = 0;

    /**
     * Cancelled reason
     *
     * @var string
     *
     * @ORM\Column(type="string", name="cancelled_reason", length=4000, nullable=true)
     */
    protected $cancelledReason;

    /**
     * Cancelled date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="cancelled_date", nullable=true)
     */
    protected $cancelledDate;

    /**
     * isAdjourned
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_adjourned", nullable=false, options={"default": 0})
     */
    protected $isAdjourned = 0;

    /**
     * Adjourned date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="adjourned_date", nullable=true)
     */
    protected $adjournedDate;

    /**
     * Adjourned reason
     *
     * @var string
     *
     * @ORM\Column(type="string", name="adjourned_reason", length=4000, nullable=true)
     */
    protected $adjournedReason;

    /**
     * Details
     *
     * @var string
     *
     * @ORM\Column(type="string", name="details", length=4000, nullable=true)
     */
    protected $details;

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
     * used to differntiate source of data during ETL when one OLCS table relates to many OLBS. Can be dropped when fully live
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;

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
     * @return PiHearing
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
     * Set the pi
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\Pi $pi new value being set
     *
     * @return PiHearing
     */
    public function setPi($pi)
    {
        $this->pi = $pi;

        return $this;
    }

    /**
     * Get the pi
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\Pi     */
    public function getPi()
    {
        return $this->pi;
    }

    /**
     * Set the presiding tc
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\PresidingTc $presidingTc new value being set
     *
     * @return PiHearing
     */
    public function setPresidingTc($presidingTc)
    {
        $this->presidingTc = $presidingTc;

        return $this;
    }

    /**
     * Get the presiding tc
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\PresidingTc     */
    public function getPresidingTc()
    {
        return $this->presidingTc;
    }

    /**
     * Set the presided by role
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $presidedByRole new value being set
     *
     * @return PiHearing
     */
    public function setPresidedByRole($presidedByRole)
    {
        $this->presidedByRole = $presidedByRole;

        return $this;
    }

    /**
     * Get the presided by role
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getPresidedByRole()
    {
        return $this->presidedByRole;
    }

    /**
     * Set the venue
     *
     * @param \Dvsa\Olcs\Api\Entity\Venue $venue new value being set
     *
     * @return PiHearing
     */
    public function setVenue($venue)
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * Get the venue
     *
     * @return \Dvsa\Olcs\Api\Entity\Venue     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return PiHearing
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
     * @return PiHearing
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
     * Set the hearing date
     *
     * @param \DateTime $hearingDate new value being set
     *
     * @return PiHearing
     */
    public function setHearingDate($hearingDate)
    {
        $this->hearingDate = $hearingDate;

        return $this;
    }

    /**
     * Get the hearing date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getHearingDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->hearingDate);
        }

        return $this->hearingDate;
    }

    /**
     * Set the is full day
     *
     * @param string $isFullDay new value being set
     *
     * @return PiHearing
     */
    public function setIsFullDay($isFullDay)
    {
        $this->isFullDay = $isFullDay;

        return $this;
    }

    /**
     * Get the is full day
     *
     * @return string     */
    public function getIsFullDay()
    {
        return $this->isFullDay;
    }

    /**
     * Set the presiding tc other
     *
     * @param string $presidingTcOther new value being set
     *
     * @return PiHearing
     */
    public function setPresidingTcOther($presidingTcOther)
    {
        $this->presidingTcOther = $presidingTcOther;

        return $this;
    }

    /**
     * Get the presiding tc other
     *
     * @return string     */
    public function getPresidingTcOther()
    {
        return $this->presidingTcOther;
    }

    /**
     * Set the venue other
     *
     * @param string $venueOther new value being set
     *
     * @return PiHearing
     */
    public function setVenueOther($venueOther)
    {
        $this->venueOther = $venueOther;

        return $this;
    }

    /**
     * Get the venue other
     *
     * @return string     */
    public function getVenueOther()
    {
        return $this->venueOther;
    }

    /**
     * Set the witnesses
     *
     * @param int $witnesses new value being set
     *
     * @return PiHearing
     */
    public function setWitnesses($witnesses)
    {
        $this->witnesses = $witnesses;

        return $this;
    }

    /**
     * Get the witnesses
     *
     * @return int     */
    public function getWitnesses()
    {
        return $this->witnesses;
    }

    /**
     * Set the drivers
     *
     * @param int $drivers new value being set
     *
     * @return PiHearing
     */
    public function setDrivers($drivers)
    {
        $this->drivers = $drivers;

        return $this;
    }

    /**
     * Get the drivers
     *
     * @return int     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * Set the is cancelled
     *
     * @param string $isCancelled new value being set
     *
     * @return PiHearing
     */
    public function setIsCancelled($isCancelled)
    {
        $this->isCancelled = $isCancelled;

        return $this;
    }

    /**
     * Get the is cancelled
     *
     * @return string     */
    public function getIsCancelled()
    {
        return $this->isCancelled;
    }

    /**
     * Set the cancelled reason
     *
     * @param string $cancelledReason new value being set
     *
     * @return PiHearing
     */
    public function setCancelledReason($cancelledReason)
    {
        $this->cancelledReason = $cancelledReason;

        return $this;
    }

    /**
     * Get the cancelled reason
     *
     * @return string     */
    public function getCancelledReason()
    {
        return $this->cancelledReason;
    }

    /**
     * Set the cancelled date
     *
     * @param \DateTime $cancelledDate new value being set
     *
     * @return PiHearing
     */
    public function setCancelledDate($cancelledDate)
    {
        $this->cancelledDate = $cancelledDate;

        return $this;
    }

    /**
     * Get the cancelled date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getCancelledDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->cancelledDate);
        }

        return $this->cancelledDate;
    }

    /**
     * Set the is adjourned
     *
     * @param string $isAdjourned new value being set
     *
     * @return PiHearing
     */
    public function setIsAdjourned($isAdjourned)
    {
        $this->isAdjourned = $isAdjourned;

        return $this;
    }

    /**
     * Get the is adjourned
     *
     * @return string     */
    public function getIsAdjourned()
    {
        return $this->isAdjourned;
    }

    /**
     * Set the adjourned date
     *
     * @param \DateTime $adjournedDate new value being set
     *
     * @return PiHearing
     */
    public function setAdjournedDate($adjournedDate)
    {
        $this->adjournedDate = $adjournedDate;

        return $this;
    }

    /**
     * Get the adjourned date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getAdjournedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->adjournedDate);
        }

        return $this->adjournedDate;
    }

    /**
     * Set the adjourned reason
     *
     * @param string $adjournedReason new value being set
     *
     * @return PiHearing
     */
    public function setAdjournedReason($adjournedReason)
    {
        $this->adjournedReason = $adjournedReason;

        return $this;
    }

    /**
     * Get the adjourned reason
     *
     * @return string     */
    public function getAdjournedReason()
    {
        return $this->adjournedReason;
    }

    /**
     * Set the details
     *
     * @param string $details new value being set
     *
     * @return PiHearing
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get the details
     *
     * @return string     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return PiHearing
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
     * @return PiHearing
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
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return PiHearing
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
