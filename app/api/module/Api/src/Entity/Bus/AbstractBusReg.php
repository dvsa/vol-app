<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

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
 * BusReg Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="bus_reg",
 *    indexes={
 *        @ORM\Index(name="ix_bus_reg_bus_notice_period_id", columns={"bus_notice_period_id"}),
 *        @ORM\Index(name="ix_bus_reg_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_bus_reg_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_bus_reg_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_bus_reg_parent_id", columns={"parent_id"}),
 *        @ORM\Index(name="ix_bus_reg_revert_status", columns={"revert_status"}),
 *        @ORM\Index(name="ix_bus_reg_status", columns={"status"}),
 *        @ORM\Index(name="ix_bus_reg_subsidised", columns={"subsidised"}),
 *        @ORM\Index(name="ix_bus_reg_withdrawn_reason", columns={"withdrawn_reason"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_bus_reg_olbs_key", columns={"olbs_key"}),
 *        @ORM\UniqueConstraint(name="uk_bus_reg_reg_no_variation_no_deleted_date",
     *     columns={"reg_no","variation_no","deleted_date"})
 *    }
 * )
 */
abstract class AbstractBusReg implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Application signed
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="application_signed", nullable=false, options={"default": 0})
     */
    protected $applicationSigned = 0;

    /**
     * Bus notice period
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod", fetch="LAZY")
     * @ORM\JoinColumn(name="bus_notice_period_id", referencedColumnName="id", nullable=false)
     */
    protected $busNoticePeriod;

    /**
     * Bus service type
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusServiceType",
     *     inversedBy="busRegs",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="bus_reg_bus_service_type",
     *     joinColumns={
     *         @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="bus_service_type_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $busServiceTypes;

    /**
     * Copied to la pte
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="copied_to_la_pte", nullable=false, options={"default": 0})
     */
    protected $copiedToLaPte = 0;

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
     * Ebsr refresh
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="ebsr_refresh", nullable=false, options={"default": 0})
     */
    protected $ebsrRefresh = 0;

    /**
     * Effective date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="effective_date", nullable=true)
     */
    protected $effectiveDate;

    /**
     * End date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="end_date", nullable=true)
     */
    protected $endDate;

    /**
     * Finish point
     *
     * @var string
     *
     * @ORM\Column(type="string", name="finish_point", length=100, nullable=true)
     */
    protected $finishPoint;

    /**
     * Has manoeuvre
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="has_manoeuvre", nullable=false, options={"default": 0})
     */
    protected $hasManoeuvre = 0;

    /**
     * Has not fixed stop
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="has_not_fixed_stop", nullable=false, options={"default": 0})
     */
    protected $hasNotFixedStop = 0;

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
     * Is quality contract
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_quality_contract", nullable=false, options={"default": 0})
     */
    protected $isQualityContract = 0;

    /**
     * Is quality partnership
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="is_quality_partnership",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $isQualityPartnership = 0;

    /**
     * Is short notice
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_short_notice", nullable=false, options={"default": 0})
     */
    protected $isShortNotice = 0;

    /**
     * Is txc app
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_txc_app", nullable=false, options={"default": 0})
     */
    protected $isTxcApp = 0;

    /**
     * La short note
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="la_short_note", nullable=false, options={"default": 0})
     */
    protected $laShortNote = 0;

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
     * Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence",
     *     fetch="LAZY",
     *     inversedBy="busRegs"
     * )
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=false)
     */
    protected $licence;

    /**
     * Local authority
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Bus\LocalAuthority",
     *     inversedBy="busRegs",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="bus_reg_local_auth",
     *     joinColumns={
     *         @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="local_authority_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $localAuthoritys;

    /**
     * Manoeuvre detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="manoeuvre_detail", length=255, nullable=true)
     */
    protected $manoeuvreDetail;

    /**
     * Map supplied
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="map_supplied", nullable=false, options={"default": 0})
     */
    protected $mapSupplied = 0;

    /**
     * Need new stop
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="need_new_stop", nullable=false, options={"default": 0})
     */
    protected $needNewStop = 0;

    /**
     * New stop detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="new_stop_detail", length=255, nullable=true)
     */
    protected $newStopDetail;

    /**
     * Not fixed stop detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="not_fixed_stop_detail", length=255, nullable=true)
     */
    protected $notFixedStopDetail;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Op notified la pte
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="op_notified_la_pte", nullable=false, options={"default": 0})
     */
    protected $opNotifiedLaPte = 0;

    /**
     * Organisation email
     *
     * @var string
     *
     * @ORM\Column(type="string", name="organisation_email", length=255, nullable=true)
     */
    protected $organisationEmail;

    /**
     * Other details
     *
     * @var string
     *
     * @ORM\Column(type="string", name="other_details", length=800, nullable=true)
     */
    protected $otherDetails;

    /**
     * Parent
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg", fetch="LAZY")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * Quality contract details
     *
     * @var string
     *
     * @ORM\Column(type="string", name="quality_contract_details", length=4000, nullable=true)
     */
    protected $qualityContractDetails;

    /**
     * Quality partnership details
     *
     * @var string
     *
     * @ORM\Column(type="string", name="quality_partnership_details", length=4000, nullable=true)
     */
    protected $qualityPartnershipDetails;

    /**
     * Quality partnership facilities used
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="quality_partnership_facilities_used",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $qualityPartnershipFacilitiesUsed = 0;

    /**
     * Reason cancelled
     *
     * @var string
     *
     * @ORM\Column(type="string", name="reason_cancelled", length=255, nullable=true)
     */
    protected $reasonCancelled;

    /**
     * Reason refused
     *
     * @var string
     *
     * @ORM\Column(type="string", name="reason_refused", length=255, nullable=true)
     */
    protected $reasonRefused;

    /**
     * Reason sn refused
     *
     * @var string
     *
     * @ORM\Column(type="string", name="reason_sn_refused", length=255, nullable=true)
     */
    protected $reasonSnRefused;

    /**
     * Received date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="received_date", nullable=true)
     */
    protected $receivedDate;

    /**
     * Reg no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="reg_no", length=70, nullable=false)
     */
    protected $regNo;

    /**
     * Revert status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="revert_status", referencedColumnName="id", nullable=false)
     */
    protected $revertStatus;

    /**
     * Route description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="route_description", length=1000, nullable=true)
     */
    protected $routeDescription;

    /**
     * Route no
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="route_no", nullable=false)
     */
    protected $routeNo;

    /**
     * Service no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="service_no", length=70, nullable=true)
     */
    protected $serviceNo;

    /**
     * Short notice refused
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="short_notice_refused",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $shortNoticeRefused = 0;

    /**
     * Start point
     *
     * @var string
     *
     * @ORM\Column(type="string", name="start_point", length=100, nullable=true)
     */
    protected $startPoint;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=false)
     */
    protected $status;

    /**
     * Status change date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="status_change_date", nullable=true)
     */
    protected $statusChangeDate;

    /**
     * Stopping arrangements
     *
     * @var string
     *
     * @ORM\Column(type="string", name="stopping_arrangements", length=800, nullable=true)
     */
    protected $stoppingArrangements;

    /**
     * Subsidised
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="subsidised", referencedColumnName="id", nullable=false)
     */
    protected $subsidised;

    /**
     * Subsidy detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="subsidy_detail", length=255, nullable=true)
     */
    protected $subsidyDetail;

    /**
     * Timetable acceptable
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="timetable_acceptable",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $timetableAcceptable = 0;

    /**
     * Traffic area
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea",
     *     inversedBy="busRegs",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="bus_reg_traffic_area",
     *     joinColumns={
     *         @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="traffic_area_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $trafficAreas;

    /**
     * Trc condition checked
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="trc_condition_checked",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $trcConditionChecked = 0;

    /**
     * Trc notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="trc_notes", length=255, nullable=true)
     */
    protected $trcNotes;

    /**
     * Txc app type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="txc_app_type", length=20, nullable=true)
     */
    protected $txcAppType;

    /**
     * Use all stops
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="use_all_stops", nullable=false, options={"default": 0})
     */
    protected $useAllStops = 0;

    /**
     * Variation no
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="variation_no", nullable=false, options={"default": 0})
     */
    protected $variationNo = 0;

    /**
     * Variation reason
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\System\RefData",
     *     inversedBy="busRegs",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="bus_reg_variation_reason",
     *     joinColumns={
     *         @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="variation_reason_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $variationReasons;

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
     * Via
     *
     * @var string
     *
     * @ORM\Column(type="string", name="via", length=255, nullable=true)
     */
    protected $via;

    /**
     * Withdrawn reason
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="withdrawn_reason", referencedColumnName="id", nullable=true)
     */
    protected $withdrawnReason;

    /**
     * Other service
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusRegOtherService",
     *     mappedBy="busReg",
     *     cascade={"persist"}
     * )
     */
    protected $otherServices;

    /**
     * Read audit
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusRegReadAudit", mappedBy="busReg")
     */
    protected $readAudits;

    /**
     * Short notice
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusShortNotice
     *
     * @ORM\OneToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusShortNotice",
     *     mappedBy="busReg",
     *     cascade={"persist"}
     * )
     */
    protected $shortNotice;

    /**
     * Document
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", mappedBy="busReg")
     */
    protected $documents;

    /**
     * Ebsr submission
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission", mappedBy="busReg")
     */
    protected $ebsrSubmissions;

    /**
     * Fee
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Fee\Fee", mappedBy="busReg")
     */
    protected $fees;

    /**
     * Publication link
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Publication\PublicationLink",
     *     mappedBy="busReg"
     * )
     */
    protected $publicationLinks;

    /**
     * Task
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Task\Task", mappedBy="busReg")
     */
    protected $tasks;

    /**
     * Txc inbox
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox",
     *     mappedBy="busReg",
     *     cascade={"persist"}
     * )
     */
    protected $txcInboxs;

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
        $this->busServiceTypes = new ArrayCollection();
        $this->localAuthoritys = new ArrayCollection();
        $this->trafficAreas = new ArrayCollection();
        $this->variationReasons = new ArrayCollection();
        $this->otherServices = new ArrayCollection();
        $this->readAudits = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->ebsrSubmissions = new ArrayCollection();
        $this->fees = new ArrayCollection();
        $this->publicationLinks = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->txcInboxs = new ArrayCollection();
    }

    /**
     * Set the application signed
     *
     * @param string $applicationSigned new value being set
     *
     * @return BusReg
     */
    public function setApplicationSigned($applicationSigned)
    {
        $this->applicationSigned = $applicationSigned;

        return $this;
    }

    /**
     * Get the application signed
     *
     * @return string
     */
    public function getApplicationSigned()
    {
        return $this->applicationSigned;
    }

    /**
     * Set the bus notice period
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod $busNoticePeriod entity being set as the value
     *
     * @return BusReg
     */
    public function setBusNoticePeriod($busNoticePeriod)
    {
        $this->busNoticePeriod = $busNoticePeriod;

        return $this;
    }

    /**
     * Get the bus notice period
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod
     */
    public function getBusNoticePeriod()
    {
        return $this->busNoticePeriod;
    }

    /**
     * Set the bus service type
     *
     * @param ArrayCollection $busServiceTypes collection being set as the value
     *
     * @return BusReg
     */
    public function setBusServiceTypes($busServiceTypes)
    {
        $this->busServiceTypes = $busServiceTypes;

        return $this;
    }

    /**
     * Get the bus service types
     *
     * @return ArrayCollection
     */
    public function getBusServiceTypes()
    {
        return $this->busServiceTypes;
    }

    /**
     * Add a bus service types
     *
     * @param ArrayCollection|mixed $busServiceTypes collection being added
     *
     * @return BusReg
     */
    public function addBusServiceTypes($busServiceTypes)
    {
        if ($busServiceTypes instanceof ArrayCollection) {
            $this->busServiceTypes = new ArrayCollection(
                array_merge(
                    $this->busServiceTypes->toArray(),
                    $busServiceTypes->toArray()
                )
            );
        } elseif (!$this->busServiceTypes->contains($busServiceTypes)) {
            $this->busServiceTypes->add($busServiceTypes);
        }

        return $this;
    }

    /**
     * Remove a bus service types
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $busServiceTypes collection being removed
     *
     * @return BusReg
     */
    public function removeBusServiceTypes($busServiceTypes)
    {
        if ($this->busServiceTypes->contains($busServiceTypes)) {
            $this->busServiceTypes->removeElement($busServiceTypes);
        }

        return $this;
    }

    /**
     * Set the copied to la pte
     *
     * @param string $copiedToLaPte new value being set
     *
     * @return BusReg
     */
    public function setCopiedToLaPte($copiedToLaPte)
    {
        $this->copiedToLaPte = $copiedToLaPte;

        return $this;
    }

    /**
     * Get the copied to la pte
     *
     * @return string
     */
    public function getCopiedToLaPte()
    {
        return $this->copiedToLaPte;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return BusReg
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
     * Set the ebsr refresh
     *
     * @param string $ebsrRefresh new value being set
     *
     * @return BusReg
     */
    public function setEbsrRefresh($ebsrRefresh)
    {
        $this->ebsrRefresh = $ebsrRefresh;

        return $this;
    }

    /**
     * Get the ebsr refresh
     *
     * @return string
     */
    public function getEbsrRefresh()
    {
        return $this->ebsrRefresh;
    }

    /**
     * Set the effective date
     *
     * @param \DateTime $effectiveDate new value being set
     *
     * @return BusReg
     */
    public function setEffectiveDate($effectiveDate)
    {
        $this->effectiveDate = $effectiveDate;

        return $this;
    }

    /**
     * Get the effective date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getEffectiveDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveDate);
        }

        return $this->effectiveDate;
    }

    /**
     * Set the end date
     *
     * @param \DateTime $endDate new value being set
     *
     * @return BusReg
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
     * @return \DateTime|string

     */
    public function getEndDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->endDate);
        }

        return $this->endDate;
    }

    /**
     * Set the finish point
     *
     * @param string $finishPoint new value being set
     *
     * @return BusReg
     */
    public function setFinishPoint($finishPoint)
    {
        $this->finishPoint = $finishPoint;

        return $this;
    }

    /**
     * Get the finish point
     *
     * @return string
     */
    public function getFinishPoint()
    {
        return $this->finishPoint;
    }

    /**
     * Set the has manoeuvre
     *
     * @param string $hasManoeuvre new value being set
     *
     * @return BusReg
     */
    public function setHasManoeuvre($hasManoeuvre)
    {
        $this->hasManoeuvre = $hasManoeuvre;

        return $this;
    }

    /**
     * Get the has manoeuvre
     *
     * @return string
     */
    public function getHasManoeuvre()
    {
        return $this->hasManoeuvre;
    }

    /**
     * Set the has not fixed stop
     *
     * @param string $hasNotFixedStop new value being set
     *
     * @return BusReg
     */
    public function setHasNotFixedStop($hasNotFixedStop)
    {
        $this->hasNotFixedStop = $hasNotFixedStop;

        return $this;
    }

    /**
     * Get the has not fixed stop
     *
     * @return string
     */
    public function getHasNotFixedStop()
    {
        return $this->hasNotFixedStop;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return BusReg
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
     * Set the is quality contract
     *
     * @param string $isQualityContract new value being set
     *
     * @return BusReg
     */
    public function setIsQualityContract($isQualityContract)
    {
        $this->isQualityContract = $isQualityContract;

        return $this;
    }

    /**
     * Get the is quality contract
     *
     * @return string
     */
    public function getIsQualityContract()
    {
        return $this->isQualityContract;
    }

    /**
     * Set the is quality partnership
     *
     * @param string $isQualityPartnership new value being set
     *
     * @return BusReg
     */
    public function setIsQualityPartnership($isQualityPartnership)
    {
        $this->isQualityPartnership = $isQualityPartnership;

        return $this;
    }

    /**
     * Get the is quality partnership
     *
     * @return string
     */
    public function getIsQualityPartnership()
    {
        return $this->isQualityPartnership;
    }

    /**
     * Set the is short notice
     *
     * @param string $isShortNotice new value being set
     *
     * @return BusReg
     */
    public function setIsShortNotice($isShortNotice)
    {
        $this->isShortNotice = $isShortNotice;

        return $this;
    }

    /**
     * Get the is short notice
     *
     * @return string
     */
    public function getIsShortNotice()
    {
        return $this->isShortNotice;
    }

    /**
     * Set the is txc app
     *
     * @param string $isTxcApp new value being set
     *
     * @return BusReg
     */
    public function setIsTxcApp($isTxcApp)
    {
        $this->isTxcApp = $isTxcApp;

        return $this;
    }

    /**
     * Get the is txc app
     *
     * @return string
     */
    public function getIsTxcApp()
    {
        return $this->isTxcApp;
    }

    /**
     * Set the la short note
     *
     * @param string $laShortNote new value being set
     *
     * @return BusReg
     */
    public function setLaShortNote($laShortNote)
    {
        $this->laShortNote = $laShortNote;

        return $this;
    }

    /**
     * Get the la short note
     *
     * @return string
     */
    public function getLaShortNote()
    {
        return $this->laShortNote;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return BusReg
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
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence entity being set as the value
     *
     * @return BusReg
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the local authority
     *
     * @param ArrayCollection $localAuthoritys collection being set as the value
     *
     * @return BusReg
     */
    public function setLocalAuthoritys($localAuthoritys)
    {
        $this->localAuthoritys = $localAuthoritys;

        return $this;
    }

    /**
     * Get the local authoritys
     *
     * @return ArrayCollection
     */
    public function getLocalAuthoritys()
    {
        return $this->localAuthoritys;
    }

    /**
     * Add a local authoritys
     *
     * @param ArrayCollection|mixed $localAuthoritys collection being added
     *
     * @return BusReg
     */
    public function addLocalAuthoritys($localAuthoritys)
    {
        if ($localAuthoritys instanceof ArrayCollection) {
            $this->localAuthoritys = new ArrayCollection(
                array_merge(
                    $this->localAuthoritys->toArray(),
                    $localAuthoritys->toArray()
                )
            );
        } elseif (!$this->localAuthoritys->contains($localAuthoritys)) {
            $this->localAuthoritys->add($localAuthoritys);
        }

        return $this;
    }

    /**
     * Remove a local authoritys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $localAuthoritys collection being removed
     *
     * @return BusReg
     */
    public function removeLocalAuthoritys($localAuthoritys)
    {
        if ($this->localAuthoritys->contains($localAuthoritys)) {
            $this->localAuthoritys->removeElement($localAuthoritys);
        }

        return $this;
    }

    /**
     * Set the manoeuvre detail
     *
     * @param string $manoeuvreDetail new value being set
     *
     * @return BusReg
     */
    public function setManoeuvreDetail($manoeuvreDetail)
    {
        $this->manoeuvreDetail = $manoeuvreDetail;

        return $this;
    }

    /**
     * Get the manoeuvre detail
     *
     * @return string
     */
    public function getManoeuvreDetail()
    {
        return $this->manoeuvreDetail;
    }

    /**
     * Set the map supplied
     *
     * @param string $mapSupplied new value being set
     *
     * @return BusReg
     */
    public function setMapSupplied($mapSupplied)
    {
        $this->mapSupplied = $mapSupplied;

        return $this;
    }

    /**
     * Get the map supplied
     *
     * @return string
     */
    public function getMapSupplied()
    {
        return $this->mapSupplied;
    }

    /**
     * Set the need new stop
     *
     * @param string $needNewStop new value being set
     *
     * @return BusReg
     */
    public function setNeedNewStop($needNewStop)
    {
        $this->needNewStop = $needNewStop;

        return $this;
    }

    /**
     * Get the need new stop
     *
     * @return string
     */
    public function getNeedNewStop()
    {
        return $this->needNewStop;
    }

    /**
     * Set the new stop detail
     *
     * @param string $newStopDetail new value being set
     *
     * @return BusReg
     */
    public function setNewStopDetail($newStopDetail)
    {
        $this->newStopDetail = $newStopDetail;

        return $this;
    }

    /**
     * Get the new stop detail
     *
     * @return string
     */
    public function getNewStopDetail()
    {
        return $this->newStopDetail;
    }

    /**
     * Set the not fixed stop detail
     *
     * @param string $notFixedStopDetail new value being set
     *
     * @return BusReg
     */
    public function setNotFixedStopDetail($notFixedStopDetail)
    {
        $this->notFixedStopDetail = $notFixedStopDetail;

        return $this;
    }

    /**
     * Get the not fixed stop detail
     *
     * @return string
     */
    public function getNotFixedStopDetail()
    {
        return $this->notFixedStopDetail;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return BusReg
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
     * Set the op notified la pte
     *
     * @param string $opNotifiedLaPte new value being set
     *
     * @return BusReg
     */
    public function setOpNotifiedLaPte($opNotifiedLaPte)
    {
        $this->opNotifiedLaPte = $opNotifiedLaPte;

        return $this;
    }

    /**
     * Get the op notified la pte
     *
     * @return string
     */
    public function getOpNotifiedLaPte()
    {
        return $this->opNotifiedLaPte;
    }

    /**
     * Set the organisation email
     *
     * @param string $organisationEmail new value being set
     *
     * @return BusReg
     */
    public function setOrganisationEmail($organisationEmail)
    {
        $this->organisationEmail = $organisationEmail;

        return $this;
    }

    /**
     * Get the organisation email
     *
     * @return string
     */
    public function getOrganisationEmail()
    {
        return $this->organisationEmail;
    }

    /**
     * Set the other details
     *
     * @param string $otherDetails new value being set
     *
     * @return BusReg
     */
    public function setOtherDetails($otherDetails)
    {
        $this->otherDetails = $otherDetails;

        return $this;
    }

    /**
     * Get the other details
     *
     * @return string
     */
    public function getOtherDetails()
    {
        return $this->otherDetails;
    }

    /**
     * Set the parent
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $parent entity being set as the value
     *
     * @return BusReg
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get the parent
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusReg
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the quality contract details
     *
     * @param string $qualityContractDetails new value being set
     *
     * @return BusReg
     */
    public function setQualityContractDetails($qualityContractDetails)
    {
        $this->qualityContractDetails = $qualityContractDetails;

        return $this;
    }

    /**
     * Get the quality contract details
     *
     * @return string
     */
    public function getQualityContractDetails()
    {
        return $this->qualityContractDetails;
    }

    /**
     * Set the quality partnership details
     *
     * @param string $qualityPartnershipDetails new value being set
     *
     * @return BusReg
     */
    public function setQualityPartnershipDetails($qualityPartnershipDetails)
    {
        $this->qualityPartnershipDetails = $qualityPartnershipDetails;

        return $this;
    }

    /**
     * Get the quality partnership details
     *
     * @return string
     */
    public function getQualityPartnershipDetails()
    {
        return $this->qualityPartnershipDetails;
    }

    /**
     * Set the quality partnership facilities used
     *
     * @param string $qualityPartnershipFacilitiesUsed new value being set
     *
     * @return BusReg
     */
    public function setQualityPartnershipFacilitiesUsed($qualityPartnershipFacilitiesUsed)
    {
        $this->qualityPartnershipFacilitiesUsed = $qualityPartnershipFacilitiesUsed;

        return $this;
    }

    /**
     * Get the quality partnership facilities used
     *
     * @return string
     */
    public function getQualityPartnershipFacilitiesUsed()
    {
        return $this->qualityPartnershipFacilitiesUsed;
    }

    /**
     * Set the reason cancelled
     *
     * @param string $reasonCancelled new value being set
     *
     * @return BusReg
     */
    public function setReasonCancelled($reasonCancelled)
    {
        $this->reasonCancelled = $reasonCancelled;

        return $this;
    }

    /**
     * Get the reason cancelled
     *
     * @return string
     */
    public function getReasonCancelled()
    {
        return $this->reasonCancelled;
    }

    /**
     * Set the reason refused
     *
     * @param string $reasonRefused new value being set
     *
     * @return BusReg
     */
    public function setReasonRefused($reasonRefused)
    {
        $this->reasonRefused = $reasonRefused;

        return $this;
    }

    /**
     * Get the reason refused
     *
     * @return string
     */
    public function getReasonRefused()
    {
        return $this->reasonRefused;
    }

    /**
     * Set the reason sn refused
     *
     * @param string $reasonSnRefused new value being set
     *
     * @return BusReg
     */
    public function setReasonSnRefused($reasonSnRefused)
    {
        $this->reasonSnRefused = $reasonSnRefused;

        return $this;
    }

    /**
     * Get the reason sn refused
     *
     * @return string
     */
    public function getReasonSnRefused()
    {
        return $this->reasonSnRefused;
    }

    /**
     * Set the received date
     *
     * @param \DateTime $receivedDate new value being set
     *
     * @return BusReg
     */
    public function setReceivedDate($receivedDate)
    {
        $this->receivedDate = $receivedDate;

        return $this;
    }

    /**
     * Get the received date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getReceivedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->receivedDate);
        }

        return $this->receivedDate;
    }

    /**
     * Set the reg no
     *
     * @param string $regNo new value being set
     *
     * @return BusReg
     */
    public function setRegNo($regNo)
    {
        $this->regNo = $regNo;

        return $this;
    }

    /**
     * Get the reg no
     *
     * @return string
     */
    public function getRegNo()
    {
        return $this->regNo;
    }

    /**
     * Set the revert status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $revertStatus entity being set as the value
     *
     * @return BusReg
     */
    public function setRevertStatus($revertStatus)
    {
        $this->revertStatus = $revertStatus;

        return $this;
    }

    /**
     * Get the revert status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getRevertStatus()
    {
        return $this->revertStatus;
    }

    /**
     * Set the route description
     *
     * @param string $routeDescription new value being set
     *
     * @return BusReg
     */
    public function setRouteDescription($routeDescription)
    {
        $this->routeDescription = $routeDescription;

        return $this;
    }

    /**
     * Get the route description
     *
     * @return string
     */
    public function getRouteDescription()
    {
        return $this->routeDescription;
    }

    /**
     * Set the route no
     *
     * @param int $routeNo new value being set
     *
     * @return BusReg
     */
    public function setRouteNo($routeNo)
    {
        $this->routeNo = $routeNo;

        return $this;
    }

    /**
     * Get the route no
     *
     * @return int
     */
    public function getRouteNo()
    {
        return $this->routeNo;
    }

    /**
     * Set the service no
     *
     * @param string $serviceNo new value being set
     *
     * @return BusReg
     */
    public function setServiceNo($serviceNo)
    {
        $this->serviceNo = $serviceNo;

        return $this;
    }

    /**
     * Get the service no
     *
     * @return string
     */
    public function getServiceNo()
    {
        return $this->serviceNo;
    }

    /**
     * Set the short notice refused
     *
     * @param string $shortNoticeRefused new value being set
     *
     * @return BusReg
     */
    public function setShortNoticeRefused($shortNoticeRefused)
    {
        $this->shortNoticeRefused = $shortNoticeRefused;

        return $this;
    }

    /**
     * Get the short notice refused
     *
     * @return string
     */
    public function getShortNoticeRefused()
    {
        return $this->shortNoticeRefused;
    }

    /**
     * Set the start point
     *
     * @param string $startPoint new value being set
     *
     * @return BusReg
     */
    public function setStartPoint($startPoint)
    {
        $this->startPoint = $startPoint;

        return $this;
    }

    /**
     * Get the start point
     *
     * @return string
     */
    public function getStartPoint()
    {
        return $this->startPoint;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status entity being set as the value
     *
     * @return BusReg
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the status change date
     *
     * @param \DateTime $statusChangeDate new value being set
     *
     * @return BusReg
     */
    public function setStatusChangeDate($statusChangeDate)
    {
        $this->statusChangeDate = $statusChangeDate;

        return $this;
    }

    /**
     * Get the status change date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getStatusChangeDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->statusChangeDate);
        }

        return $this->statusChangeDate;
    }

    /**
     * Set the stopping arrangements
     *
     * @param string $stoppingArrangements new value being set
     *
     * @return BusReg
     */
    public function setStoppingArrangements($stoppingArrangements)
    {
        $this->stoppingArrangements = $stoppingArrangements;

        return $this;
    }

    /**
     * Get the stopping arrangements
     *
     * @return string
     */
    public function getStoppingArrangements()
    {
        return $this->stoppingArrangements;
    }

    /**
     * Set the subsidised
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $subsidised entity being set as the value
     *
     * @return BusReg
     */
    public function setSubsidised($subsidised)
    {
        $this->subsidised = $subsidised;

        return $this;
    }

    /**
     * Get the subsidised
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getSubsidised()
    {
        return $this->subsidised;
    }

    /**
     * Set the subsidy detail
     *
     * @param string $subsidyDetail new value being set
     *
     * @return BusReg
     */
    public function setSubsidyDetail($subsidyDetail)
    {
        $this->subsidyDetail = $subsidyDetail;

        return $this;
    }

    /**
     * Get the subsidy detail
     *
     * @return string
     */
    public function getSubsidyDetail()
    {
        return $this->subsidyDetail;
    }

    /**
     * Set the timetable acceptable
     *
     * @param string $timetableAcceptable new value being set
     *
     * @return BusReg
     */
    public function setTimetableAcceptable($timetableAcceptable)
    {
        $this->timetableAcceptable = $timetableAcceptable;

        return $this;
    }

    /**
     * Get the timetable acceptable
     *
     * @return string
     */
    public function getTimetableAcceptable()
    {
        return $this->timetableAcceptable;
    }

    /**
     * Set the traffic area
     *
     * @param ArrayCollection $trafficAreas collection being set as the value
     *
     * @return BusReg
     */
    public function setTrafficAreas($trafficAreas)
    {
        $this->trafficAreas = $trafficAreas;

        return $this;
    }

    /**
     * Get the traffic areas
     *
     * @return ArrayCollection
     */
    public function getTrafficAreas()
    {
        return $this->trafficAreas;
    }

    /**
     * Add a traffic areas
     *
     * @param ArrayCollection|mixed $trafficAreas collection being added
     *
     * @return BusReg
     */
    public function addTrafficAreas($trafficAreas)
    {
        if ($trafficAreas instanceof ArrayCollection) {
            $this->trafficAreas = new ArrayCollection(
                array_merge(
                    $this->trafficAreas->toArray(),
                    $trafficAreas->toArray()
                )
            );
        } elseif (!$this->trafficAreas->contains($trafficAreas)) {
            $this->trafficAreas->add($trafficAreas);
        }

        return $this;
    }

    /**
     * Remove a traffic areas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $trafficAreas collection being removed
     *
     * @return BusReg
     */
    public function removeTrafficAreas($trafficAreas)
    {
        if ($this->trafficAreas->contains($trafficAreas)) {
            $this->trafficAreas->removeElement($trafficAreas);
        }

        return $this;
    }

    /**
     * Set the trc condition checked
     *
     * @param string $trcConditionChecked new value being set
     *
     * @return BusReg
     */
    public function setTrcConditionChecked($trcConditionChecked)
    {
        $this->trcConditionChecked = $trcConditionChecked;

        return $this;
    }

    /**
     * Get the trc condition checked
     *
     * @return string
     */
    public function getTrcConditionChecked()
    {
        return $this->trcConditionChecked;
    }

    /**
     * Set the trc notes
     *
     * @param string $trcNotes new value being set
     *
     * @return BusReg
     */
    public function setTrcNotes($trcNotes)
    {
        $this->trcNotes = $trcNotes;

        return $this;
    }

    /**
     * Get the trc notes
     *
     * @return string
     */
    public function getTrcNotes()
    {
        return $this->trcNotes;
    }

    /**
     * Set the txc app type
     *
     * @param string $txcAppType new value being set
     *
     * @return BusReg
     */
    public function setTxcAppType($txcAppType)
    {
        $this->txcAppType = $txcAppType;

        return $this;
    }

    /**
     * Get the txc app type
     *
     * @return string
     */
    public function getTxcAppType()
    {
        return $this->txcAppType;
    }

    /**
     * Set the use all stops
     *
     * @param string $useAllStops new value being set
     *
     * @return BusReg
     */
    public function setUseAllStops($useAllStops)
    {
        $this->useAllStops = $useAllStops;

        return $this;
    }

    /**
     * Get the use all stops
     *
     * @return string
     */
    public function getUseAllStops()
    {
        return $this->useAllStops;
    }

    /**
     * Set the variation no
     *
     * @param int $variationNo new value being set
     *
     * @return BusReg
     */
    public function setVariationNo($variationNo)
    {
        $this->variationNo = $variationNo;

        return $this;
    }

    /**
     * Get the variation no
     *
     * @return int
     */
    public function getVariationNo()
    {
        return $this->variationNo;
    }

    /**
     * Set the variation reason
     *
     * @param ArrayCollection $variationReasons collection being set as the value
     *
     * @return BusReg
     */
    public function setVariationReasons($variationReasons)
    {
        $this->variationReasons = $variationReasons;

        return $this;
    }

    /**
     * Get the variation reasons
     *
     * @return ArrayCollection
     */
    public function getVariationReasons()
    {
        return $this->variationReasons;
    }

    /**
     * Add a variation reasons
     *
     * @param ArrayCollection|mixed $variationReasons collection being added
     *
     * @return BusReg
     */
    public function addVariationReasons($variationReasons)
    {
        if ($variationReasons instanceof ArrayCollection) {
            $this->variationReasons = new ArrayCollection(
                array_merge(
                    $this->variationReasons->toArray(),
                    $variationReasons->toArray()
                )
            );
        } elseif (!$this->variationReasons->contains($variationReasons)) {
            $this->variationReasons->add($variationReasons);
        }

        return $this;
    }

    /**
     * Remove a variation reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $variationReasons collection being removed
     *
     * @return BusReg
     */
    public function removeVariationReasons($variationReasons)
    {
        if ($this->variationReasons->contains($variationReasons)) {
            $this->variationReasons->removeElement($variationReasons);
        }

        return $this;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return BusReg
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
     * Set the via
     *
     * @param string $via new value being set
     *
     * @return BusReg
     */
    public function setVia($via)
    {
        $this->via = $via;

        return $this;
    }

    /**
     * Get the via
     *
     * @return string
     */
    public function getVia()
    {
        return $this->via;
    }

    /**
     * Set the withdrawn reason
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $withdrawnReason entity being set as the value
     *
     * @return BusReg
     */
    public function setWithdrawnReason($withdrawnReason)
    {
        $this->withdrawnReason = $withdrawnReason;

        return $this;
    }

    /**
     * Get the withdrawn reason
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getWithdrawnReason()
    {
        return $this->withdrawnReason;
    }

    /**
     * Set the other service
     *
     * @param ArrayCollection $otherServices collection being set as the value
     *
     * @return BusReg
     */
    public function setOtherServices($otherServices)
    {
        $this->otherServices = $otherServices;

        return $this;
    }

    /**
     * Get the other services
     *
     * @return ArrayCollection
     */
    public function getOtherServices()
    {
        return $this->otherServices;
    }

    /**
     * Add a other services
     *
     * @param ArrayCollection|mixed $otherServices collection being added
     *
     * @return BusReg
     */
    public function addOtherServices($otherServices)
    {
        if ($otherServices instanceof ArrayCollection) {
            $this->otherServices = new ArrayCollection(
                array_merge(
                    $this->otherServices->toArray(),
                    $otherServices->toArray()
                )
            );
        } elseif (!$this->otherServices->contains($otherServices)) {
            $this->otherServices->add($otherServices);
        }

        return $this;
    }

    /**
     * Remove a other services
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $otherServices collection being removed
     *
     * @return BusReg
     */
    public function removeOtherServices($otherServices)
    {
        if ($this->otherServices->contains($otherServices)) {
            $this->otherServices->removeElement($otherServices);
        }

        return $this;
    }

    /**
     * Set the read audit
     *
     * @param ArrayCollection $readAudits collection being set as the value
     *
     * @return BusReg
     */
    public function setReadAudits($readAudits)
    {
        $this->readAudits = $readAudits;

        return $this;
    }

    /**
     * Get the read audits
     *
     * @return ArrayCollection
     */
    public function getReadAudits()
    {
        return $this->readAudits;
    }

    /**
     * Add a read audits
     *
     * @param ArrayCollection|mixed $readAudits collection being added
     *
     * @return BusReg
     */
    public function addReadAudits($readAudits)
    {
        if ($readAudits instanceof ArrayCollection) {
            $this->readAudits = new ArrayCollection(
                array_merge(
                    $this->readAudits->toArray(),
                    $readAudits->toArray()
                )
            );
        } elseif (!$this->readAudits->contains($readAudits)) {
            $this->readAudits->add($readAudits);
        }

        return $this;
    }

    /**
     * Remove a read audits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $readAudits collection being removed
     *
     * @return BusReg
     */
    public function removeReadAudits($readAudits)
    {
        if ($this->readAudits->contains($readAudits)) {
            $this->readAudits->removeElement($readAudits);
        }

        return $this;
    }

    /**
     * Set the short notice
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusShortNotice $shortNotice entity being set as the value
     *
     * @return BusReg
     */
    public function setShortNotice($shortNotice)
    {
        $this->shortNotice = $shortNotice;

        return $this;
    }

    /**
     * Get the short notice
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusShortNotice
     */
    public function getShortNotice()
    {
        return $this->shortNotice;
    }

    /**
     * Set the document
     *
     * @param ArrayCollection $documents collection being set as the value
     *
     * @return BusReg
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get the documents
     *
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add a documents
     *
     * @param ArrayCollection|mixed $documents collection being added
     *
     * @return BusReg
     */
    public function addDocuments($documents)
    {
        if ($documents instanceof ArrayCollection) {
            $this->documents = new ArrayCollection(
                array_merge(
                    $this->documents->toArray(),
                    $documents->toArray()
                )
            );
        } elseif (!$this->documents->contains($documents)) {
            $this->documents->add($documents);
        }

        return $this;
    }

    /**
     * Remove a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being removed
     *
     * @return BusReg
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Set the ebsr submission
     *
     * @param ArrayCollection $ebsrSubmissions collection being set as the value
     *
     * @return BusReg
     */
    public function setEbsrSubmissions($ebsrSubmissions)
    {
        $this->ebsrSubmissions = $ebsrSubmissions;

        return $this;
    }

    /**
     * Get the ebsr submissions
     *
     * @return ArrayCollection
     */
    public function getEbsrSubmissions()
    {
        return $this->ebsrSubmissions;
    }

    /**
     * Add a ebsr submissions
     *
     * @param ArrayCollection|mixed $ebsrSubmissions collection being added
     *
     * @return BusReg
     */
    public function addEbsrSubmissions($ebsrSubmissions)
    {
        if ($ebsrSubmissions instanceof ArrayCollection) {
            $this->ebsrSubmissions = new ArrayCollection(
                array_merge(
                    $this->ebsrSubmissions->toArray(),
                    $ebsrSubmissions->toArray()
                )
            );
        } elseif (!$this->ebsrSubmissions->contains($ebsrSubmissions)) {
            $this->ebsrSubmissions->add($ebsrSubmissions);
        }

        return $this;
    }

    /**
     * Remove a ebsr submissions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $ebsrSubmissions collection being removed
     *
     * @return BusReg
     */
    public function removeEbsrSubmissions($ebsrSubmissions)
    {
        if ($this->ebsrSubmissions->contains($ebsrSubmissions)) {
            $this->ebsrSubmissions->removeElement($ebsrSubmissions);
        }

        return $this;
    }

    /**
     * Set the fee
     *
     * @param ArrayCollection $fees collection being set as the value
     *
     * @return BusReg
     */
    public function setFees($fees)
    {
        $this->fees = $fees;

        return $this;
    }

    /**
     * Get the fees
     *
     * @return ArrayCollection
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Add a fees
     *
     * @param ArrayCollection|mixed $fees collection being added
     *
     * @return BusReg
     */
    public function addFees($fees)
    {
        if ($fees instanceof ArrayCollection) {
            $this->fees = new ArrayCollection(
                array_merge(
                    $this->fees->toArray(),
                    $fees->toArray()
                )
            );
        } elseif (!$this->fees->contains($fees)) {
            $this->fees->add($fees);
        }

        return $this;
    }

    /**
     * Remove a fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $fees collection being removed
     *
     * @return BusReg
     */
    public function removeFees($fees)
    {
        if ($this->fees->contains($fees)) {
            $this->fees->removeElement($fees);
        }

        return $this;
    }

    /**
     * Set the publication link
     *
     * @param ArrayCollection $publicationLinks collection being set as the value
     *
     * @return BusReg
     */
    public function setPublicationLinks($publicationLinks)
    {
        $this->publicationLinks = $publicationLinks;

        return $this;
    }

    /**
     * Get the publication links
     *
     * @return ArrayCollection
     */
    public function getPublicationLinks()
    {
        return $this->publicationLinks;
    }

    /**
     * Add a publication links
     *
     * @param ArrayCollection|mixed $publicationLinks collection being added
     *
     * @return BusReg
     */
    public function addPublicationLinks($publicationLinks)
    {
        if ($publicationLinks instanceof ArrayCollection) {
            $this->publicationLinks = new ArrayCollection(
                array_merge(
                    $this->publicationLinks->toArray(),
                    $publicationLinks->toArray()
                )
            );
        } elseif (!$this->publicationLinks->contains($publicationLinks)) {
            $this->publicationLinks->add($publicationLinks);
        }

        return $this;
    }

    /**
     * Remove a publication links
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $publicationLinks collection being removed
     *
     * @return BusReg
     */
    public function removePublicationLinks($publicationLinks)
    {
        if ($this->publicationLinks->contains($publicationLinks)) {
            $this->publicationLinks->removeElement($publicationLinks);
        }

        return $this;
    }

    /**
     * Set the task
     *
     * @param ArrayCollection $tasks collection being set as the value
     *
     * @return BusReg
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * Get the tasks
     *
     * @return ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Add a tasks
     *
     * @param ArrayCollection|mixed $tasks collection being added
     *
     * @return BusReg
     */
    public function addTasks($tasks)
    {
        if ($tasks instanceof ArrayCollection) {
            $this->tasks = new ArrayCollection(
                array_merge(
                    $this->tasks->toArray(),
                    $tasks->toArray()
                )
            );
        } elseif (!$this->tasks->contains($tasks)) {
            $this->tasks->add($tasks);
        }

        return $this;
    }

    /**
     * Remove a tasks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tasks collection being removed
     *
     * @return BusReg
     */
    public function removeTasks($tasks)
    {
        if ($this->tasks->contains($tasks)) {
            $this->tasks->removeElement($tasks);
        }

        return $this;
    }

    /**
     * Set the txc inbox
     *
     * @param ArrayCollection $txcInboxs collection being set as the value
     *
     * @return BusReg
     */
    public function setTxcInboxs($txcInboxs)
    {
        $this->txcInboxs = $txcInboxs;

        return $this;
    }

    /**
     * Get the txc inboxs
     *
     * @return ArrayCollection
     */
    public function getTxcInboxs()
    {
        return $this->txcInboxs;
    }

    /**
     * Add a txc inboxs
     *
     * @param ArrayCollection|mixed $txcInboxs collection being added
     *
     * @return BusReg
     */
    public function addTxcInboxs($txcInboxs)
    {
        if ($txcInboxs instanceof ArrayCollection) {
            $this->txcInboxs = new ArrayCollection(
                array_merge(
                    $this->txcInboxs->toArray(),
                    $txcInboxs->toArray()
                )
            );
        } elseif (!$this->txcInboxs->contains($txcInboxs)) {
            $this->txcInboxs->add($txcInboxs);
        }

        return $this;
    }

    /**
     * Remove a txc inboxs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $txcInboxs collection being removed
     *
     * @return BusReg
     */
    public function removeTxcInboxs($txcInboxs)
    {
        if ($this->txcInboxs->contains($txcInboxs)) {
            $this->txcInboxs->removeElement($txcInboxs);
        }

        return $this;
    }
}
