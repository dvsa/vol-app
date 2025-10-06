<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Bus;

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
 * AbstractBusShortNotice Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="bus_short_notice",
 *    indexes={
 *        @ORM\Index(name="ix_bus_short_notice_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_bus_short_notice_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="uk_bus_short_notice_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="uk_bus_short_notice_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_bus_short_notice_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\UniqueConstraint(name="uk_bus_short_notice_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractBusShortNotice implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to bus_reg
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg", fetch="LAZY")
     * @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id")
     */
    protected $busReg;

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
     * bankHolidayChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="bank_holiday_change", nullable=false, options={"default": 0})
     */
    protected $bankHolidayChange = 0;

    /**
     * unforseenChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="unforseen_change", nullable=false, options={"default": 0})
     */
    protected $unforseenChange = 0;

    /**
     * Unforseen detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="unforseen_detail", length=255, nullable=true)
     */
    protected $unforseenDetail;

    /**
     * timetableChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="timetable_change", nullable=false, options={"default": 0})
     */
    protected $timetableChange = 0;

    /**
     * Timetable detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="timetable_detail", length=255, nullable=true)
     */
    protected $timetableDetail;

    /**
     * replacementChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="replacement_change", nullable=false, options={"default": 0})
     */
    protected $replacementChange = 0;

    /**
     * Replacement detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="replacement_detail", length=255, nullable=true)
     */
    protected $replacementDetail;

    /**
     * holidayChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="holiday_change", nullable=false, options={"default": 0})
     */
    protected $holidayChange = 0;

    /**
     * Holiday detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="holiday_detail", length=255, nullable=true)
     */
    protected $holidayDetail;

    /**
     * trcChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="trc_change", nullable=false, options={"default": 0})
     */
    protected $trcChange = 0;

    /**
     * Trc detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="trc_detail", length=255, nullable=true)
     */
    protected $trcDetail;

    /**
     * policeChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="police_change", nullable=false, options={"default": 0})
     */
    protected $policeChange = 0;

    /**
     * Police detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="police_detail", length=255, nullable=true)
     */
    protected $policeDetail;

    /**
     * specialOccasionChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="special_occasion_change", nullable=false, options={"default": 0})
     */
    protected $specialOccasionChange = 0;

    /**
     * Special occasion detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="special_occasion_detail", length=255, nullable=true)
     */
    protected $specialOccasionDetail;

    /**
     * connectionChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="connection_change", nullable=false, options={"default": 0})
     */
    protected $connectionChange = 0;

    /**
     * Connection detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="connection_detail", length=255, nullable=true)
     */
    protected $connectionDetail;

    /**
     * notAvailableChange
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="not_available_change", nullable=false, options={"default": 0})
     */
    protected $notAvailableChange = 0;

    /**
     * Not available detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="not_available_detail", length=255, nullable=true)
     */
    protected $notAvailableDetail;

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
     * @return BusShortNotice
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
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg new value being set
     *
     * @return BusShortNotice
     */
    public function setBusReg($busReg)
    {
        $this->busReg = $busReg;

        return $this;
    }

    /**
     * Get the bus reg
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusReg     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return BusShortNotice
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
     * @return BusShortNotice
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
     * Set the bank holiday change
     *
     * @param string $bankHolidayChange new value being set
     *
     * @return BusShortNotice
     */
    public function setBankHolidayChange($bankHolidayChange)
    {
        $this->bankHolidayChange = $bankHolidayChange;

        return $this;
    }

    /**
     * Get the bank holiday change
     *
     * @return string     */
    public function getBankHolidayChange()
    {
        return $this->bankHolidayChange;
    }

    /**
     * Set the unforseen change
     *
     * @param string $unforseenChange new value being set
     *
     * @return BusShortNotice
     */
    public function setUnforseenChange($unforseenChange)
    {
        $this->unforseenChange = $unforseenChange;

        return $this;
    }

    /**
     * Get the unforseen change
     *
     * @return string     */
    public function getUnforseenChange()
    {
        return $this->unforseenChange;
    }

    /**
     * Set the unforseen detail
     *
     * @param string $unforseenDetail new value being set
     *
     * @return BusShortNotice
     */
    public function setUnforseenDetail($unforseenDetail)
    {
        $this->unforseenDetail = $unforseenDetail;

        return $this;
    }

    /**
     * Get the unforseen detail
     *
     * @return string     */
    public function getUnforseenDetail()
    {
        return $this->unforseenDetail;
    }

    /**
     * Set the timetable change
     *
     * @param string $timetableChange new value being set
     *
     * @return BusShortNotice
     */
    public function setTimetableChange($timetableChange)
    {
        $this->timetableChange = $timetableChange;

        return $this;
    }

    /**
     * Get the timetable change
     *
     * @return string     */
    public function getTimetableChange()
    {
        return $this->timetableChange;
    }

    /**
     * Set the timetable detail
     *
     * @param string $timetableDetail new value being set
     *
     * @return BusShortNotice
     */
    public function setTimetableDetail($timetableDetail)
    {
        $this->timetableDetail = $timetableDetail;

        return $this;
    }

    /**
     * Get the timetable detail
     *
     * @return string     */
    public function getTimetableDetail()
    {
        return $this->timetableDetail;
    }

    /**
     * Set the replacement change
     *
     * @param string $replacementChange new value being set
     *
     * @return BusShortNotice
     */
    public function setReplacementChange($replacementChange)
    {
        $this->replacementChange = $replacementChange;

        return $this;
    }

    /**
     * Get the replacement change
     *
     * @return string     */
    public function getReplacementChange()
    {
        return $this->replacementChange;
    }

    /**
     * Set the replacement detail
     *
     * @param string $replacementDetail new value being set
     *
     * @return BusShortNotice
     */
    public function setReplacementDetail($replacementDetail)
    {
        $this->replacementDetail = $replacementDetail;

        return $this;
    }

    /**
     * Get the replacement detail
     *
     * @return string     */
    public function getReplacementDetail()
    {
        return $this->replacementDetail;
    }

    /**
     * Set the holiday change
     *
     * @param string $holidayChange new value being set
     *
     * @return BusShortNotice
     */
    public function setHolidayChange($holidayChange)
    {
        $this->holidayChange = $holidayChange;

        return $this;
    }

    /**
     * Get the holiday change
     *
     * @return string     */
    public function getHolidayChange()
    {
        return $this->holidayChange;
    }

    /**
     * Set the holiday detail
     *
     * @param string $holidayDetail new value being set
     *
     * @return BusShortNotice
     */
    public function setHolidayDetail($holidayDetail)
    {
        $this->holidayDetail = $holidayDetail;

        return $this;
    }

    /**
     * Get the holiday detail
     *
     * @return string     */
    public function getHolidayDetail()
    {
        return $this->holidayDetail;
    }

    /**
     * Set the trc change
     *
     * @param string $trcChange new value being set
     *
     * @return BusShortNotice
     */
    public function setTrcChange($trcChange)
    {
        $this->trcChange = $trcChange;

        return $this;
    }

    /**
     * Get the trc change
     *
     * @return string     */
    public function getTrcChange()
    {
        return $this->trcChange;
    }

    /**
     * Set the trc detail
     *
     * @param string $trcDetail new value being set
     *
     * @return BusShortNotice
     */
    public function setTrcDetail($trcDetail)
    {
        $this->trcDetail = $trcDetail;

        return $this;
    }

    /**
     * Get the trc detail
     *
     * @return string     */
    public function getTrcDetail()
    {
        return $this->trcDetail;
    }

    /**
     * Set the police change
     *
     * @param string $policeChange new value being set
     *
     * @return BusShortNotice
     */
    public function setPoliceChange($policeChange)
    {
        $this->policeChange = $policeChange;

        return $this;
    }

    /**
     * Get the police change
     *
     * @return string     */
    public function getPoliceChange()
    {
        return $this->policeChange;
    }

    /**
     * Set the police detail
     *
     * @param string $policeDetail new value being set
     *
     * @return BusShortNotice
     */
    public function setPoliceDetail($policeDetail)
    {
        $this->policeDetail = $policeDetail;

        return $this;
    }

    /**
     * Get the police detail
     *
     * @return string     */
    public function getPoliceDetail()
    {
        return $this->policeDetail;
    }

    /**
     * Set the special occasion change
     *
     * @param string $specialOccasionChange new value being set
     *
     * @return BusShortNotice
     */
    public function setSpecialOccasionChange($specialOccasionChange)
    {
        $this->specialOccasionChange = $specialOccasionChange;

        return $this;
    }

    /**
     * Get the special occasion change
     *
     * @return string     */
    public function getSpecialOccasionChange()
    {
        return $this->specialOccasionChange;
    }

    /**
     * Set the special occasion detail
     *
     * @param string $specialOccasionDetail new value being set
     *
     * @return BusShortNotice
     */
    public function setSpecialOccasionDetail($specialOccasionDetail)
    {
        $this->specialOccasionDetail = $specialOccasionDetail;

        return $this;
    }

    /**
     * Get the special occasion detail
     *
     * @return string     */
    public function getSpecialOccasionDetail()
    {
        return $this->specialOccasionDetail;
    }

    /**
     * Set the connection change
     *
     * @param string $connectionChange new value being set
     *
     * @return BusShortNotice
     */
    public function setConnectionChange($connectionChange)
    {
        $this->connectionChange = $connectionChange;

        return $this;
    }

    /**
     * Get the connection change
     *
     * @return string     */
    public function getConnectionChange()
    {
        return $this->connectionChange;
    }

    /**
     * Set the connection detail
     *
     * @param string $connectionDetail new value being set
     *
     * @return BusShortNotice
     */
    public function setConnectionDetail($connectionDetail)
    {
        $this->connectionDetail = $connectionDetail;

        return $this;
    }

    /**
     * Get the connection detail
     *
     * @return string     */
    public function getConnectionDetail()
    {
        return $this->connectionDetail;
    }

    /**
     * Set the not available change
     *
     * @param string $notAvailableChange new value being set
     *
     * @return BusShortNotice
     */
    public function setNotAvailableChange($notAvailableChange)
    {
        $this->notAvailableChange = $notAvailableChange;

        return $this;
    }

    /**
     * Get the not available change
     *
     * @return string     */
    public function getNotAvailableChange()
    {
        return $this->notAvailableChange;
    }

    /**
     * Set the not available detail
     *
     * @param string $notAvailableDetail new value being set
     *
     * @return BusShortNotice
     */
    public function setNotAvailableDetail($notAvailableDetail)
    {
        $this->notAvailableDetail = $notAvailableDetail;

        return $this;
    }

    /**
     * Get the not available detail
     *
     * @return string     */
    public function getNotAvailableDetail()
    {
        return $this->notAvailableDetail;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return BusShortNotice
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
     * @return BusShortNotice
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