<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PiHearing Abstract Entity
 *
 * Auto-Generated
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
 *        @ORM\Index(name="ix_pi_hearing_venue_id", columns={"venue_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_pi_hearing_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
abstract class AbstractPiHearing implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Cancelled date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="cancelled_date", nullable=true)
     */
    protected $cancelledDate;

    /**
     * Cancelled reason
     *
     * @var string
     *
     * @ORM\Column(type="string", name="cancelled_reason", length=4000, nullable=true)
     */
    protected $cancelledReason;

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
     * Details
     *
     * @var string
     *
     * @ORM\Column(type="string", name="details", length=4000, nullable=true)
     */
    protected $details;

    /**
     * Drivers
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="drivers", nullable=true)
     */
    protected $drivers;

    /**
     * Hearing date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="hearing_date", nullable=true)
     */
    protected $hearingDate;

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
     * Is adjourned
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_adjourned", nullable=false, options={"default": 0})
     */
    protected $isAdjourned = 0;

    /**
     * Is cancelled
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_cancelled", nullable=false, options={"default": 0})
     */
    protected $isCancelled = 0;

    /**
     * Is full day
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_full_day", nullable=true)
     */
    protected $isFullDay;

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
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Olbs type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;

    /**
     * Pi
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\Pi
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\Pi", fetch="LAZY", inversedBy="piHearings")
     * @ORM\JoinColumn(name="pi_id", referencedColumnName="id", nullable=false)
     */
    protected $pi;

    /**
     * Presided by role
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="presided_by_role", referencedColumnName="id", nullable=true)
     */
    protected $presidedByRole;

    /**
     * Presiding tc
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\PresidingTc
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\PresidingTc", fetch="LAZY")
     * @ORM\JoinColumn(name="presiding_tc_id", referencedColumnName="id", nullable=true)
     */
    protected $presidingTc;

    /**
     * Presiding tc other
     *
     * @var string
     *
     * @ORM\Column(type="string", name="presiding_tc_other", length=45, nullable=true)
     */
    protected $presidingTcOther;

    /**
     * Venue
     *
     * @var \Dvsa\Olcs\Api\Entity\Venue
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Venue", fetch="LAZY")
     * @ORM\JoinColumn(name="venue_id", referencedColumnName="id", nullable=true)
     */
    protected $venue;

    /**
     * Venue other
     *
     * @var string
     *
     * @ORM\Column(type="string", name="venue_other", length=255, nullable=true)
     */
    protected $venueOther;

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
     * Witnesses
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="witnesses", nullable=true)
     */
    protected $witnesses;

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
     * @return \DateTime|string

     */
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
     * @return string
     */
    public function getAdjournedReason()
    {
        return $this->adjournedReason;
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
     * @return \DateTime|string

     */
    public function getCancelledDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->cancelledDate);
        }

        return $this->cancelledDate;
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
     * @return string
     */
    public function getCancelledReason()
    {
        return $this->cancelledReason;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
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
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
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
     * @return int
     */
    public function getDrivers()
    {
        return $this->drivers;
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
     * @return \DateTime|string

     */
    public function getHearingDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->hearingDate);
        }

        return $this->hearingDate;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return string
     */
    public function getIsAdjourned()
    {
        return $this->isAdjourned;
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
     * @return string
     */
    public function getIsCancelled()
    {
        return $this->isCancelled;
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
     * @return string
     */
    public function getIsFullDay()
    {
        return $this->isFullDay;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
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
     * @return int
     */
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
     * @return string
     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Set the pi
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\Pi $pi entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\Pi\Pi
     */
    public function getPi()
    {
        return $this->pi;
    }

    /**
     * Set the presided by role
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $presidedByRole entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getPresidedByRole()
    {
        return $this->presidedByRole;
    }

    /**
     * Set the presiding tc
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\PresidingTc $presidingTc entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\Pi\PresidingTc
     */
    public function getPresidingTc()
    {
        return $this->presidingTc;
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
     * @return string
     */
    public function getPresidingTcOther()
    {
        return $this->presidingTcOther;
    }

    /**
     * Set the venue
     *
     * @param \Dvsa\Olcs\Api\Entity\Venue $venue entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\Venue
     */
    public function getVenue()
    {
        return $this->venue;
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
     * @return string
     */
    public function getVenueOther()
    {
        return $this->venueOther;
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
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
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
     * @return int
     */
    public function getWitnesses()
    {
        return $this->witnesses;
    }
}
