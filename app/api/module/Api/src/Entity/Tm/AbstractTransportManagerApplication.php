<?php

declare(strict_types=1);

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
 * AbstractTransportManagerApplication Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="transport_manager_application",
 *    indexes={
 *        @ORM\Index(name="ix_op_application_op_digital_signature_id", columns={"op_digital_signature_id"}),
 *        @ORM\Index(name="ix_op_application_op_signature_type", columns={"op_signature_type"}),
 *        @ORM\Index(name="ix_tm_application_tm_digital_signature_id", columns={"tm_digital_signature_id"}),
 *        @ORM\Index(name="ix_tm_application_tm_signature_type", columns={"tm_signature_type"}),
 *        @ORM\Index(name="ix_transport_manager_application_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_transport_manager_application_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_transport_manager_application_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_transport_manager_application_tm_application_status", columns={"tm_application_status"}),
 *        @ORM\Index(name="ix_transport_manager_application_tm_type", columns={"tm_type"}),
 *        @ORM\Index(name="ix_transport_manager_application_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="uk_transport_manager_application_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_transport_manager_application_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractTransportManagerApplication implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to transport_manager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id")
     */
    protected $transportManager;

    /**
     * Foreign Key to application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\Application", fetch="LAZY")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     */
    protected $application;

    /**
     * TmType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_type", referencedColumnName="id", nullable=true)
     */
    protected $tmType;

    /**
     * type of signature used from ref data one of sig_physical_signature   the application is signed with a physical signature sig_digital_signature   the application is signed digitally sig_signature_not_required
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_signature_type", referencedColumnName="id", nullable=true)
     */
    protected $tmSignatureType;

    /**
     * id of the TM Verify signature where applicable.
     *
     * @var \Dvsa\Olcs\Api\Entity\DigitalSignature
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\DigitalSignature", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_digital_signature_id", referencedColumnName="id", nullable=true)
     */
    protected $tmDigitalSignature;

    /**
     * type of signature used from ref data one of sig_physical_signature   the application is signed with a physical signature sig_digital_signature   the application is signed digitally sig_signature_not_required
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="op_signature_type", referencedColumnName="id", nullable=true)
     */
    protected $opSignatureType;

    /**
     * id of the Operator Verify signature where applicable.
     *
     * @var \Dvsa\Olcs\Api\Entity\DigitalSignature
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\DigitalSignature", fetch="LAZY")
     * @ORM\JoinColumn(name="op_digital_signature_id", referencedColumnName="id", nullable=true)
     */
    protected $opDigitalSignature;

    /**
     * TmApplicationStatus
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_application_status", referencedColumnName="id", nullable=true)
     */
    protected $tmApplicationStatus;

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
     * isOwner
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_owner", nullable=true)
     */
    protected $isOwner;

    /**
     * A or D for Add or Delete
     *
     * @var string
     *
     * @ORM\Column(type="string", name="action", length=1, nullable=false)
     */
    protected $action = '';

    /**
     * Hours mon
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_mon", nullable=true)
     */
    protected $hoursMon;

    /**
     * Hours tue
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_tue", nullable=true)
     */
    protected $hoursTue;

    /**
     * Hours wed
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_wed", nullable=true)
     */
    protected $hoursWed;

    /**
     * Hours thu
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_thu", nullable=true)
     */
    protected $hoursThu;

    /**
     * Hours fri
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_fri", nullable=true)
     */
    protected $hoursFri;

    /**
     * Hours sat
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_sat", nullable=true)
     */
    protected $hoursSat;

    /**
     * Hours sun
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_sun", nullable=true)
     */
    protected $hoursSun;

    /**
     * Has other licences
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="has_other_licences", nullable=true)
     */
    protected $hasOtherLicences;

    /**
     * Has other employment
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="has_other_employment", nullable=true)
     */
    protected $hasOtherEmployment;

    /**
     * Has convictions
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="has_convictions", nullable=true)
     */
    protected $hasConvictions;

    /**
     * Has previous licences
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="has_previous_licences", nullable=true)
     */
    protected $hasPreviousLicences;

    /**
     * Whether TM has undertaken training in last 5 years - added November 2021
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="has_undertaken_training", nullable=true)
     */
    protected $hasUndertakenTraining;

    /**
     * declarationConfirmation
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="declaration_confirmation", nullable=false, options={"default": 0})
     */
    protected $declarationConfirmation = 0;

    /**
     * Additional information
     *
     * @var string
     *
     * @ORM\Column(type="string", name="additional_information", length=4000, nullable=true)
     */
    protected $additionalInformation;

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
     * OtherLicences
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence", mappedBy="transportManagerApplication")
     */
    protected $otherLicences;

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
        $this->otherLicences = new ArrayCollection();
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
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Application\Application     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the tm type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tmType new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getTmType()
    {
        return $this->tmType;
    }

    /**
     * Set the tm signature type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tmSignatureType new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getTmSignatureType()
    {
        return $this->tmSignatureType;
    }

    /**
     * Set the tm digital signature
     *
     * @param \Dvsa\Olcs\Api\Entity\DigitalSignature $tmDigitalSignature new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\DigitalSignature     */
    public function getTmDigitalSignature()
    {
        return $this->tmDigitalSignature;
    }

    /**
     * Set the op signature type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $opSignatureType new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getOpSignatureType()
    {
        return $this->opSignatureType;
    }

    /**
     * Set the op digital signature
     *
     * @param \Dvsa\Olcs\Api\Entity\DigitalSignature $opDigitalSignature new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\DigitalSignature     */
    public function getOpDigitalSignature()
    {
        return $this->opDigitalSignature;
    }

    /**
     * Set the tm application status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tmApplicationStatus new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getTmApplicationStatus()
    {
        return $this->tmApplicationStatus;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
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
     * @return string     */
    public function getIsOwner()
    {
        return $this->isOwner;
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
     * @return string     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the hours mon
     *
     * @param string $hoursMon new value being set
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
     * @return string     */
    public function getHoursMon()
    {
        return $this->hoursMon;
    }

    /**
     * Set the hours tue
     *
     * @param string $hoursTue new value being set
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
     * @return string     */
    public function getHoursTue()
    {
        return $this->hoursTue;
    }

    /**
     * Set the hours wed
     *
     * @param string $hoursWed new value being set
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
     * @return string     */
    public function getHoursWed()
    {
        return $this->hoursWed;
    }

    /**
     * Set the hours thu
     *
     * @param string $hoursThu new value being set
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
     * @return string     */
    public function getHoursThu()
    {
        return $this->hoursThu;
    }

    /**
     * Set the hours fri
     *
     * @param string $hoursFri new value being set
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
     * @return string     */
    public function getHoursFri()
    {
        return $this->hoursFri;
    }

    /**
     * Set the hours sat
     *
     * @param string $hoursSat new value being set
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
     * @return string     */
    public function getHoursSat()
    {
        return $this->hoursSat;
    }

    /**
     * Set the hours sun
     *
     * @param string $hoursSun new value being set
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
     * @return string     */
    public function getHoursSun()
    {
        return $this->hoursSun;
    }

    /**
     * Set the has other licences
     *
     * @param bool $hasOtherLicences new value being set
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
     * @return bool     */
    public function getHasOtherLicences()
    {
        return $this->hasOtherLicences;
    }

    /**
     * Set the has other employment
     *
     * @param bool $hasOtherEmployment new value being set
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
     * @return bool     */
    public function getHasOtherEmployment()
    {
        return $this->hasOtherEmployment;
    }

    /**
     * Set the has convictions
     *
     * @param bool $hasConvictions new value being set
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
     * @return bool     */
    public function getHasConvictions()
    {
        return $this->hasConvictions;
    }

    /**
     * Set the has previous licences
     *
     * @param bool $hasPreviousLicences new value being set
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
     * @return bool     */
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
     * @return string     */
    public function getHasUndertakenTraining()
    {
        return $this->hasUndertakenTraining;
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
     * @return string     */
    public function getDeclarationConfirmation()
    {
        return $this->declarationConfirmation;
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
     * @return string     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
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
     * @return int     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $otherLicences collection being set as the value
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOtherLicences()
    {
        return $this->otherLicences;
    }

    /**
     * Add a other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $otherLicences collection being added
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

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
