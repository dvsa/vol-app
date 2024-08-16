<?php

namespace Dvsa\Olcs\Api\Entity\Tm;

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
 * TransportManagerApplication Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="transport_manager_application",
 *    indexes={
 *        @ORM\Index(name="ix_op_application_op_digital_signature_id",
     *     columns={"op_digital_signature_id"}),
 *        @ORM\Index(name="ix_op_application_op_signature_type", columns={"op_signature_type"}),
 *        @ORM\Index(name="ix_tm_application_tm_digital_signature_id",
     *     columns={"tm_digital_signature_id"}),
 *        @ORM\Index(name="ix_tm_application_tm_signature_type", columns={"tm_signature_type"}),
 *        @ORM\Index(name="ix_transport_manager_application_application_id",
     *     columns={"application_id"}),
 *        @ORM\Index(name="ix_transport_manager_application_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_transport_manager_application_last_modified_by",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_transport_manager_application_tm_application_status",
     *     columns={"tm_application_status"}),
 *        @ORM\Index(name="ix_transport_manager_application_tm_type", columns={"tm_type"}),
 *        @ORM\Index(name="ix_transport_manager_application_transport_manager_id",
     *     columns={"transport_manager_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_transport_manager_application_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractTransportManagerApplication implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Action
     *
     * @var string
     *
     * @ORM\Column(type="string", name="action", length=1, nullable=false)
     */
    protected $action;

    /**
     * Additional information
     *
     * @var string
     *
     * @ORM\Column(type="string", name="additional_information", length=4000, nullable=true)
     */
    protected $additionalInformation;

    /**
     * Application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\Application",
     *     fetch="LAZY",
     *     inversedBy="transportManagers"
     * )
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=false)
     */
    protected $application;

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
     * Declaration confirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="declaration_confirmation",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $declarationConfirmation = 0;

    /**
     * Has convictions
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="has_convictions", nullable=true)
     */
    protected $hasConvictions;

    /**
     * Has other employment
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="has_other_employment", nullable=true)
     */
    protected $hasOtherEmployment;

    /**
     * Has other licences
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="has_other_licences", nullable=true)
     */
    protected $hasOtherLicences;

    /**
     * Has previous licences
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="has_previous_licences", nullable=true)
     */
    protected $hasPreviousLicences;

    /**
     * Has undertaken training
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="has_undertaken_training", nullable=true)
     */
    protected $hasUndertakenTraining;

    /**
     * Hours fri
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="hours_fri", precision=3, scale=1, nullable=true)
     */
    protected $hoursFri;

    /**
     * Hours mon
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="hours_mon", precision=3, scale=1, nullable=true)
     */
    protected $hoursMon;

    /**
     * Hours sat
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="hours_sat", precision=3, scale=1, nullable=true)
     */
    protected $hoursSat;

    /**
     * Hours sun
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="hours_sun", precision=3, scale=1, nullable=true)
     */
    protected $hoursSun;

    /**
     * Hours thu
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="hours_thu", precision=3, scale=1, nullable=true)
     */
    protected $hoursThu;

    /**
     * Hours tue
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="hours_tue", precision=3, scale=1, nullable=true)
     */
    protected $hoursTue;

    /**
     * Hours wed
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="hours_wed", precision=3, scale=1, nullable=true)
     */
    protected $hoursWed;

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
     * Is owner
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_owner", nullable=true)
     */
    protected $isOwner;

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
     * Op digital signature
     *
     * @var \Dvsa\Olcs\Api\Entity\DigitalSignature
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\DigitalSignature", fetch="LAZY")
     * @ORM\JoinColumn(name="op_digital_signature_id", referencedColumnName="id", nullable=true)
     */
    protected $opDigitalSignature;

    /**
     * Op signature type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="op_signature_type", referencedColumnName="id", nullable=true)
     */
    protected $opSignatureType;

    /**
     * Tm application status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_application_status", referencedColumnName="id", nullable=true)
     */
    protected $tmApplicationStatus;

    /**
     * Tm digital signature
     *
     * @var \Dvsa\Olcs\Api\Entity\DigitalSignature
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\DigitalSignature", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_digital_signature_id", referencedColumnName="id", nullable=true)
     */
    protected $tmDigitalSignature;

    /**
     * Tm signature type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_signature_type", referencedColumnName="id", nullable=true)
     */
    protected $tmSignatureType;

    /**
     * Tm type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_type", referencedColumnName="id", nullable=true)
     */
    protected $tmType;

    /**
     * Transport manager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager",
     *     fetch="LAZY",
     *     inversedBy="tmApplications"
     * )
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id", nullable=false)
     */
    protected $transportManager;

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
     * Other licence
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence",
     *     mappedBy="transportManagerApplication"
     * )
     */
    protected $otherLicences;

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
        $this->otherLicences = new ArrayCollection();
    }

    /**
     * Set the action
     *
     * @param string $action new value being set
     *
     * @return TransportManagerApplication
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the additional information
     *
     * @param string $additionalInformation new value being set
     *
     * @return TransportManagerApplication
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;

        return $this;
    }

    /**
     * Get the additional information
     *
     * @return string
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    /**
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application entity being set as the value
     *
     * @return TransportManagerApplication
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get the application
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return TransportManagerApplication
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
     * Set the declaration confirmation
     *
     * @param string $declarationConfirmation new value being set
     *
     * @return TransportManagerApplication
     */
    public function setDeclarationConfirmation($declarationConfirmation)
    {
        $this->declarationConfirmation = $declarationConfirmation;

        return $this;
    }

    /**
     * Get the declaration confirmation
     *
     * @return string
     */
    public function getDeclarationConfirmation()
    {
        return $this->declarationConfirmation;
    }

    /**
     * Set the has convictions
     *
     * @param boolean $hasConvictions new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHasConvictions($hasConvictions)
    {
        $this->hasConvictions = $hasConvictions;

        return $this;
    }

    /**
     * Get the has convictions
     *
     * @return boolean
     */
    public function getHasConvictions()
    {
        return $this->hasConvictions;
    }

    /**
     * Set the has other employment
     *
     * @param boolean $hasOtherEmployment new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHasOtherEmployment($hasOtherEmployment)
    {
        $this->hasOtherEmployment = $hasOtherEmployment;

        return $this;
    }

    /**
     * Get the has other employment
     *
     * @return boolean
     */
    public function getHasOtherEmployment()
    {
        return $this->hasOtherEmployment;
    }

    /**
     * Set the has other licences
     *
     * @param boolean $hasOtherLicences new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHasOtherLicences($hasOtherLicences)
    {
        $this->hasOtherLicences = $hasOtherLicences;

        return $this;
    }

    /**
     * Get the has other licences
     *
     * @return boolean
     */
    public function getHasOtherLicences()
    {
        return $this->hasOtherLicences;
    }

    /**
     * Set the has previous licences
     *
     * @param boolean $hasPreviousLicences new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHasPreviousLicences($hasPreviousLicences)
    {
        $this->hasPreviousLicences = $hasPreviousLicences;

        return $this;
    }

    /**
     * Get the has previous licences
     *
     * @return boolean
     */
    public function getHasPreviousLicences()
    {
        return $this->hasPreviousLicences;
    }

    /**
     * Set the has undertaken training
     *
     * @param string $hasUndertakenTraining new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHasUndertakenTraining($hasUndertakenTraining)
    {
        $this->hasUndertakenTraining = $hasUndertakenTraining;

        return $this;
    }

    /**
     * Get the has undertaken training
     *
     * @return string
     */
    public function getHasUndertakenTraining()
    {
        return $this->hasUndertakenTraining;
    }

    /**
     * Set the hours fri
     *
     * @param float $hoursFri new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHoursFri($hoursFri)
    {
        $this->hoursFri = $hoursFri;

        return $this;
    }

    /**
     * Get the hours fri
     *
     * @return float
     */
    public function getHoursFri()
    {
        return $this->hoursFri;
    }

    /**
     * Set the hours mon
     *
     * @param float $hoursMon new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHoursMon($hoursMon)
    {
        $this->hoursMon = $hoursMon;

        return $this;
    }

    /**
     * Get the hours mon
     *
     * @return float
     */
    public function getHoursMon()
    {
        return $this->hoursMon;
    }

    /**
     * Set the hours sat
     *
     * @param float $hoursSat new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHoursSat($hoursSat)
    {
        $this->hoursSat = $hoursSat;

        return $this;
    }

    /**
     * Get the hours sat
     *
     * @return float
     */
    public function getHoursSat()
    {
        return $this->hoursSat;
    }

    /**
     * Set the hours sun
     *
     * @param float $hoursSun new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHoursSun($hoursSun)
    {
        $this->hoursSun = $hoursSun;

        return $this;
    }

    /**
     * Get the hours sun
     *
     * @return float
     */
    public function getHoursSun()
    {
        return $this->hoursSun;
    }

    /**
     * Set the hours thu
     *
     * @param float $hoursThu new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHoursThu($hoursThu)
    {
        $this->hoursThu = $hoursThu;

        return $this;
    }

    /**
     * Get the hours thu
     *
     * @return float
     */
    public function getHoursThu()
    {
        return $this->hoursThu;
    }

    /**
     * Set the hours tue
     *
     * @param float $hoursTue new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHoursTue($hoursTue)
    {
        $this->hoursTue = $hoursTue;

        return $this;
    }

    /**
     * Get the hours tue
     *
     * @return float
     */
    public function getHoursTue()
    {
        return $this->hoursTue;
    }

    /**
     * Set the hours wed
     *
     * @param float $hoursWed new value being set
     *
     * @return TransportManagerApplication
     */
    public function setHoursWed($hoursWed)
    {
        $this->hoursWed = $hoursWed;

        return $this;
    }

    /**
     * Get the hours wed
     *
     * @return float
     */
    public function getHoursWed()
    {
        return $this->hoursWed;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return TransportManagerApplication
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
     * Set the is owner
     *
     * @param string $isOwner new value being set
     *
     * @return TransportManagerApplication
     */
    public function setIsOwner($isOwner)
    {
        $this->isOwner = $isOwner;

        return $this;
    }

    /**
     * Get the is owner
     *
     * @return string
     */
    public function getIsOwner()
    {
        return $this->isOwner;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return TransportManagerApplication
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
     * @return TransportManagerApplication
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
     * Set the op digital signature
     *
     * @param \Dvsa\Olcs\Api\Entity\DigitalSignature $opDigitalSignature entity being set as the value
     *
     * @return TransportManagerApplication
     */
    public function setOpDigitalSignature($opDigitalSignature)
    {
        $this->opDigitalSignature = $opDigitalSignature;

        return $this;
    }

    /**
     * Get the op digital signature
     *
     * @return \Dvsa\Olcs\Api\Entity\DigitalSignature
     */
    public function getOpDigitalSignature()
    {
        return $this->opDigitalSignature;
    }

    /**
     * Set the op signature type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $opSignatureType entity being set as the value
     *
     * @return TransportManagerApplication
     */
    public function setOpSignatureType($opSignatureType)
    {
        $this->opSignatureType = $opSignatureType;

        return $this;
    }

    /**
     * Get the op signature type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getOpSignatureType()
    {
        return $this->opSignatureType;
    }

    /**
     * Set the tm application status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tmApplicationStatus entity being set as the value
     *
     * @return TransportManagerApplication
     */
    public function setTmApplicationStatus($tmApplicationStatus)
    {
        $this->tmApplicationStatus = $tmApplicationStatus;

        return $this;
    }

    /**
     * Get the tm application status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getTmApplicationStatus()
    {
        return $this->tmApplicationStatus;
    }

    /**
     * Set the tm digital signature
     *
     * @param \Dvsa\Olcs\Api\Entity\DigitalSignature $tmDigitalSignature entity being set as the value
     *
     * @return TransportManagerApplication
     */
    public function setTmDigitalSignature($tmDigitalSignature)
    {
        $this->tmDigitalSignature = $tmDigitalSignature;

        return $this;
    }

    /**
     * Get the tm digital signature
     *
     * @return \Dvsa\Olcs\Api\Entity\DigitalSignature
     */
    public function getTmDigitalSignature()
    {
        return $this->tmDigitalSignature;
    }

    /**
     * Set the tm signature type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tmSignatureType entity being set as the value
     *
     * @return TransportManagerApplication
     */
    public function setTmSignatureType($tmSignatureType)
    {
        $this->tmSignatureType = $tmSignatureType;

        return $this;
    }

    /**
     * Get the tm signature type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getTmSignatureType()
    {
        return $this->tmSignatureType;
    }

    /**
     * Set the tm type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tmType entity being set as the value
     *
     * @return TransportManagerApplication
     */
    public function setTmType($tmType)
    {
        $this->tmType = $tmType;

        return $this;
    }

    /**
     * Get the tm type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getTmType()
    {
        return $this->tmType;
    }

    /**
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager entity being set as the value
     *
     * @return TransportManagerApplication
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;

        return $this;
    }

    /**
     * Get the transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return TransportManagerApplication
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
     * Set the other licence
     *
     * @param ArrayCollection $otherLicences collection being set as the value
     *
     * @return TransportManagerApplication
     */
    public function setOtherLicences($otherLicences)
    {
        $this->otherLicences = $otherLicences;

        return $this;
    }

    /**
     * Get the other licences
     *
     * @return ArrayCollection
     */
    public function getOtherLicences()
    {
        return $this->otherLicences;
    }

    /**
     * Add a other licences
     *
     * @param ArrayCollection|mixed $otherLicences collection being added
     *
     * @return TransportManagerApplication
     */
    public function addOtherLicences($otherLicences)
    {
        if ($otherLicences instanceof ArrayCollection) {
            $this->otherLicences = new ArrayCollection(
                array_merge(
                    $this->otherLicences->toArray(),
                    $otherLicences->toArray()
                )
            );
        } elseif (!$this->otherLicences->contains($otherLicences)) {
            $this->otherLicences->add($otherLicences);
        }

        return $this;
    }

    /**
     * Remove a other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $otherLicences collection being removed
     *
     * @return TransportManagerApplication
     */
    public function removeOtherLicences($otherLicences)
    {
        if ($this->otherLicences->contains($otherLicences)) {
            $this->otherLicences->removeElement($otherLicences);
        }

        return $this;
    }
}
