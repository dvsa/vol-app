<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Irfo;

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
 * AbstractIrfoPsvAuth Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irfo_psv_auth",
 *    indexes={
 *        @ORM\Index(name="ix_irfo_psv_auth_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_irfo_psv_auth_type_id", columns={"irfo_psv_auth_type_id"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_journey_frequency", columns={"journey_frequency"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_status", columns={"status"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_withdrawn_reason", columns={"withdrawn_reason"})
 *    }
 * )
 */
abstract class AbstractIrfoPsvAuth implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to organisation
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Organisation", fetch="LAZY")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     */
    protected $organisation;

    /**
     * Foreign Key to irfo_psv_auth_type
     *
     * @var \Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType", fetch="LAZY")
     * @ORM\JoinColumn(name="irfo_psv_auth_type_id", referencedColumnName="id")
     */
    protected $irfoPsvAuthType;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id")
     */
    protected $status;

    /**
     * JourneyFrequency
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="journey_frequency", referencedColumnName="id", nullable=true)
     */
    protected $journeyFrequency;

    /**
     * WithdrawnReason
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="withdrawn_reason", referencedColumnName="id", nullable=true)
     */
    protected $withdrawnReason;

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
     * Exemption details
     *
     * @var string
     *
     * @ORM\Column(type="string", name="exemption_details", length=255, nullable=true)
     */
    protected $exemptionDetails;

    /**
     * Expiry date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="expiry_date", nullable=true)
     */
    protected $expiryDate;

    /**
     * isFeeExemptApplication
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_fee_exempt_application", nullable=false, options={"default": 0})
     */
    protected $isFeeExemptApplication = 0;

    /**
     * isFeeExemptAnnual
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_fee_exempt_annual", nullable=false, options={"default": 0})
     */
    protected $isFeeExemptAnnual = 0;

    /**
     * In force date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="in_force_date", nullable=true)
     */
    protected $inForceDate;

    /**
     * Irfo fee id
     *
     * @var string
     *
     * @ORM\Column(type="string", name="irfo_fee_id", length=10, nullable=true)
     */
    protected $irfoFeeId;

    /**
     * Irfo file no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="irfo_file_no", length=10, nullable=false)
     */
    protected $irfoFileNo = '';

    /**
     * Copies issued
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="copies_issued", nullable=false, options={"default": 0})
     */
    protected $copiesIssued = 0;

    /**
     * Copies required
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="copies_required", nullable=false, options={"default": 0})
     */
    protected $copiesRequired = 0;

    /**
     * Copies required total
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="copies_required_total", nullable=false, options={"default": 0})
     */
    protected $copiesRequiredTotal = 0;

    /**
     * Copies issued total
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="copies_issued_total", nullable=false, options={"default": 0})
     */
    protected $copiesIssuedTotal = 0;

    /**
     * Last date copies req
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_date_copies_req", nullable=true)
     */
    protected $lastDateCopiesReq;

    /**
     * Renewal date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="renewal_date", nullable=true)
     */
    protected $renewalDate;

    /**
     * Service route from
     *
     * @var string
     *
     * @ORM\Column(type="string", name="service_route_from", length=30, nullable=false)
     */
    protected $serviceRouteFrom = '';

    /**
     * Service route to
     *
     * @var string
     *
     * @ORM\Column(type="string", name="service_route_to", length=30, nullable=false)
     */
    protected $serviceRouteTo = '';

    /**
     * Years valid for.  Some negative numbers in legacy hence signed.
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="validity_period", nullable=false)
     */
    protected $validityPeriod = 0;

    /**
     * Authorisation must be reviewed within x days of this date for approval.  sla related.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="application_sent_date", nullable=true)
     */
    protected $applicationSentDate;

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
     * Countrys
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\Country", inversedBy="irfoPsvAuths", fetch="LAZY")
     * @ORM\JoinTable(name="irfo_psv_auth_country",
     *     joinColumns={
     *         @ORM\JoinColumn(name="irfo_psv_auth_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $countrys;

    /**
     * IrfoPsvAuthNumbers
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthNumber", mappedBy="irfoPsvAuth", cascade={"persist"})
     */
    protected $irfoPsvAuthNumbers;

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
        $this->countrys = new ArrayCollection();
        $this->irfoPsvAuthNumbers = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return IrfoPsvAuth
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
     * Set the organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get the organisation
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set the irfo psv auth type
     *
     * @param \Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType $irfoPsvAuthType new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setIrfoPsvAuthType($irfoPsvAuthType)
    {
        $this->irfoPsvAuthType = $irfoPsvAuthType;

        return $this;
    }

    /**
     * Get the irfo psv auth type
     *
     * @return \Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType     */
    public function getIrfoPsvAuthType()
    {
        return $this->irfoPsvAuthType;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the journey frequency
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $journeyFrequency new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setJourneyFrequency($journeyFrequency)
    {
        $this->journeyFrequency = $journeyFrequency;

        return $this;
    }

    /**
     * Get the journey frequency
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getJourneyFrequency()
    {
        return $this->journeyFrequency;
    }

    /**
     * Set the withdrawn reason
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $withdrawnReason new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setWithdrawnReason($withdrawnReason)
    {
        $this->withdrawnReason = $withdrawnReason;

        return $this;
    }

    /**
     * Get the withdrawn reason
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getWithdrawnReason()
    {
        return $this->withdrawnReason;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return IrfoPsvAuth
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
     * @return IrfoPsvAuth
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
     * Set the exemption details
     *
     * @param string $exemptionDetails new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setExemptionDetails($exemptionDetails)
    {
        $this->exemptionDetails = $exemptionDetails;

        return $this;
    }

    /**
     * Get the exemption details
     *
     * @return string     */
    public function getExemptionDetails()
    {
        return $this->exemptionDetails;
    }

    /**
     * Set the expiry date
     *
     * @param \DateTime $expiryDate new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get the expiry date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getExpiryDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->expiryDate);
        }

        return $this->expiryDate;
    }

    /**
     * Set the is fee exempt application
     *
     * @param string $isFeeExemptApplication new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setIsFeeExemptApplication($isFeeExemptApplication)
    {
        $this->isFeeExemptApplication = $isFeeExemptApplication;

        return $this;
    }

    /**
     * Get the is fee exempt application
     *
     * @return string     */
    public function getIsFeeExemptApplication()
    {
        return $this->isFeeExemptApplication;
    }

    /**
     * Set the is fee exempt annual
     *
     * @param string $isFeeExemptAnnual new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setIsFeeExemptAnnual($isFeeExemptAnnual)
    {
        $this->isFeeExemptAnnual = $isFeeExemptAnnual;

        return $this;
    }

    /**
     * Get the is fee exempt annual
     *
     * @return string     */
    public function getIsFeeExemptAnnual()
    {
        return $this->isFeeExemptAnnual;
    }

    /**
     * Set the in force date
     *
     * @param \DateTime $inForceDate new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setInForceDate($inForceDate)
    {
        $this->inForceDate = $inForceDate;

        return $this;
    }

    /**
     * Get the in force date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getInForceDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->inForceDate);
        }

        return $this->inForceDate;
    }

    /**
     * Set the irfo fee id
     *
     * @param string $irfoFeeId new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setIrfoFeeId($irfoFeeId)
    {
        $this->irfoFeeId = $irfoFeeId;

        return $this;
    }

    /**
     * Get the irfo fee id
     *
     * @return string     */
    public function getIrfoFeeId()
    {
        return $this->irfoFeeId;
    }

    /**
     * Set the irfo file no
     *
     * @param string $irfoFileNo new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setIrfoFileNo($irfoFileNo)
    {
        $this->irfoFileNo = $irfoFileNo;

        return $this;
    }

    /**
     * Get the irfo file no
     *
     * @return string     */
    public function getIrfoFileNo()
    {
        return $this->irfoFileNo;
    }

    /**
     * Set the copies issued
     *
     * @param int $copiesIssued new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setCopiesIssued($copiesIssued)
    {
        $this->copiesIssued = $copiesIssued;

        return $this;
    }

    /**
     * Get the copies issued
     *
     * @return int     */
    public function getCopiesIssued()
    {
        return $this->copiesIssued;
    }

    /**
     * Set the copies required
     *
     * @param int $copiesRequired new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setCopiesRequired($copiesRequired)
    {
        $this->copiesRequired = $copiesRequired;

        return $this;
    }

    /**
     * Get the copies required
     *
     * @return int     */
    public function getCopiesRequired()
    {
        return $this->copiesRequired;
    }

    /**
     * Set the copies required total
     *
     * @param int $copiesRequiredTotal new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setCopiesRequiredTotal($copiesRequiredTotal)
    {
        $this->copiesRequiredTotal = $copiesRequiredTotal;

        return $this;
    }

    /**
     * Get the copies required total
     *
     * @return int     */
    public function getCopiesRequiredTotal()
    {
        return $this->copiesRequiredTotal;
    }

    /**
     * Set the copies issued total
     *
     * @param int $copiesIssuedTotal new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setCopiesIssuedTotal($copiesIssuedTotal)
    {
        $this->copiesIssuedTotal = $copiesIssuedTotal;

        return $this;
    }

    /**
     * Get the copies issued total
     *
     * @return int     */
    public function getCopiesIssuedTotal()
    {
        return $this->copiesIssuedTotal;
    }

    /**
     * Set the last date copies req
     *
     * @param \DateTime $lastDateCopiesReq new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setLastDateCopiesReq($lastDateCopiesReq)
    {
        $this->lastDateCopiesReq = $lastDateCopiesReq;

        return $this;
    }

    /**
     * Get the last date copies req
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getLastDateCopiesReq($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastDateCopiesReq);
        }

        return $this->lastDateCopiesReq;
    }

    /**
     * Set the renewal date
     *
     * @param \DateTime $renewalDate new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setRenewalDate($renewalDate)
    {
        $this->renewalDate = $renewalDate;

        return $this;
    }

    /**
     * Get the renewal date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRenewalDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->renewalDate);
        }

        return $this->renewalDate;
    }

    /**
     * Set the service route from
     *
     * @param string $serviceRouteFrom new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setServiceRouteFrom($serviceRouteFrom)
    {
        $this->serviceRouteFrom = $serviceRouteFrom;

        return $this;
    }

    /**
     * Get the service route from
     *
     * @return string     */
    public function getServiceRouteFrom()
    {
        return $this->serviceRouteFrom;
    }

    /**
     * Set the service route to
     *
     * @param string $serviceRouteTo new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setServiceRouteTo($serviceRouteTo)
    {
        $this->serviceRouteTo = $serviceRouteTo;

        return $this;
    }

    /**
     * Get the service route to
     *
     * @return string     */
    public function getServiceRouteTo()
    {
        return $this->serviceRouteTo;
    }

    /**
     * Set the validity period
     *
     * @param int $validityPeriod new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setValidityPeriod($validityPeriod)
    {
        $this->validityPeriod = $validityPeriod;

        return $this;
    }

    /**
     * Get the validity period
     *
     * @return int     */
    public function getValidityPeriod()
    {
        return $this->validityPeriod;
    }

    /**
     * Set the application sent date
     *
     * @param \DateTime $applicationSentDate new value being set
     *
     * @return IrfoPsvAuth
     */
    public function setApplicationSentDate($applicationSentDate)
    {
        $this->applicationSentDate = $applicationSentDate;

        return $this;
    }

    /**
     * Get the application sent date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getApplicationSentDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->applicationSentDate);
        }

        return $this->applicationSentDate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrfoPsvAuth
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
     * Set the countrys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $countrys collection being set as the value
     *
     * @return IrfoPsvAuth
     */
    public function setCountrys($countrys)
    {
        $this->countrys = $countrys;

        return $this;
    }

    /**
     * Get the countrys
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCountrys()
    {
        return $this->countrys;
    }

    /**
     * Add a countrys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $countrys collection being added
     *
     * @return IrfoPsvAuth
     */
    public function addCountrys($countrys)
    {
        if ($countrys instanceof ArrayCollection) {
            $this->countrys = new ArrayCollection(
                array_merge(
                    $this->countrys->toArray(),
                    $countrys->toArray()
                )
            );
        } elseif (!$this->countrys->contains($countrys)) {
            $this->countrys->add($countrys);
        }

        return $this;
    }

    /**
     * Remove a countrys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $countrys collection being removed
     *
     * @return IrfoPsvAuth
     */
    public function removeCountrys($countrys)
    {
        if ($this->countrys->contains($countrys)) {
            $this->countrys->removeElement($countrys);
        }

        return $this;
    }

    /**
     * Set the irfo psv auth numbers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irfoPsvAuthNumbers collection being set as the value
     *
     * @return IrfoPsvAuth
     */
    public function setIrfoPsvAuthNumbers($irfoPsvAuthNumbers)
    {
        $this->irfoPsvAuthNumbers = $irfoPsvAuthNumbers;

        return $this;
    }

    /**
     * Get the irfo psv auth numbers
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrfoPsvAuthNumbers()
    {
        return $this->irfoPsvAuthNumbers;
    }

    /**
     * Add a irfo psv auth numbers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irfoPsvAuthNumbers collection being added
     *
     * @return IrfoPsvAuth
     */
    public function addIrfoPsvAuthNumbers($irfoPsvAuthNumbers)
    {
        if ($irfoPsvAuthNumbers instanceof ArrayCollection) {
            $this->irfoPsvAuthNumbers = new ArrayCollection(
                array_merge(
                    $this->irfoPsvAuthNumbers->toArray(),
                    $irfoPsvAuthNumbers->toArray()
                )
            );
        } elseif (!$this->irfoPsvAuthNumbers->contains($irfoPsvAuthNumbers)) {
            $this->irfoPsvAuthNumbers->add($irfoPsvAuthNumbers);
        }

        return $this;
    }

    /**
     * Remove a irfo psv auth numbers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irfoPsvAuthNumbers collection being removed
     *
     * @return IrfoPsvAuth
     */
    public function removeIrfoPsvAuthNumbers($irfoPsvAuthNumbers)
    {
        if ($this->irfoPsvAuthNumbers->contains($irfoPsvAuthNumbers)) {
            $this->irfoPsvAuthNumbers->removeElement($irfoPsvAuthNumbers);
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