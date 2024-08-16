<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

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
 * Organisation Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="organisation",
 *    indexes={
 *        @ORM\Index(name="ix_organisation_contact_details_id", columns={"contact_details_id"}),
 *        @ORM\Index(name="ix_organisation_cpid", columns={"cpid"}),
 *        @ORM\Index(name="ix_organisation_cpid_name", columns={"cpid","name"}),
 *        @ORM\Index(name="ix_organisation_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_organisation_irfo_contact_details_id",
     *     columns={"irfo_contact_details_id"}),
 *        @ORM\Index(name="ix_organisation_irfo_nationality", columns={"irfo_nationality"}),
 *        @ORM\Index(name="ix_organisation_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_organisation_lead_tc_area_id", columns={"lead_tc_area_id"}),
 *        @ORM\Index(name="ix_organisation_name", columns={"name"}),
 *        @ORM\Index(name="ix_organisation_type", columns={"type"})
 *    }
 * )
 */
abstract class AbstractOrganisation implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Allow email
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="allow_email", nullable=false, options={"default": 0})
     */
    protected $allowEmail = 0;

    /**
     * Company cert seen
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="company_cert_seen", nullable=false, options={"default": 0})
     */
    protected $companyCertSeen = 0;

    /**
     * Company or llp no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="company_or_llp_no", length=20, nullable=true)
     */
    protected $companyOrLlpNo;

    /**
     * Confirm share trailer info
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="confirm_share_trailer_info",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $confirmShareTrailerInfo = 0;

    /**
     * Confirm share vehicle info
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="confirm_share_vehicle_info",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $confirmShareVehicleInfo = 0;

    /**
     * Contact details
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails", fetch="LAZY")
     * @ORM\JoinColumn(name="contact_details_id", referencedColumnName="id", nullable=true)
     */
    protected $contactDetails;

    /**
     * Cpid
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="cpid", referencedColumnName="id", nullable=true)
     */
    protected $cpid;

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
     * Irfo contact details
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails",
     *     fetch="LAZY",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="irfo_contact_details_id", referencedColumnName="id", nullable=true)
     */
    protected $irfoContactDetails;

    /**
     * Irfo nationality
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\Country
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\Country", fetch="LAZY")
     * @ORM\JoinColumn(name="irfo_nationality", referencedColumnName="id", nullable=true)
     */
    protected $irfoNationality;

    /**
     * Is irfo
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_irfo", nullable=false, options={"default": 0})
     */
    protected $isIrfo = 0;

    /**
     * Is messaging disabled
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean",
     *     name="is_messaging_disabled",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $isMessagingDisabled = 0;

    /**
     * Is messaging file upload enabled
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean",
     *     name="is_messaging_file_upload_enabled",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $isMessagingFileUploadEnabled = 0;

    /**
     * Is unlicensed
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_unlicensed", nullable=false, options={"default": 0})
     */
    protected $isUnlicensed = 0;

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
     * Lead tc area
     *
     * @var \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea", fetch="LAZY")
     * @ORM\JoinColumn(name="lead_tc_area_id", referencedColumnName="id", nullable=true)
     */
    protected $leadTcArea;

    /**
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=160, nullable=true)
     */
    protected $name;

    /**
     * Nature of business
     *
     * @var string
     *
     * @ORM\Column(type="string", name="nature_of_business", length=255, nullable=true)
     */
    protected $natureOfBusiness;

    /**
     * Type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="type", referencedColumnName="id", nullable=false)
     */
    protected $type;

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
     * Disqualification
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Disqualification",
     *     mappedBy="organisation"
     * )
     */
    protected $disqualifications;

    /**
     * Irfo partner
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Irfo\IrfoPartner",
     *     mappedBy="organisation",
     *     cascade={"persist"}
     * )
     */
    protected $irfoPartners;

    /**
     * Licence
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", mappedBy="organisation")
     */
    protected $licences;

    /**
     * Organisation person
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson",
     *     mappedBy="organisation"
     * )
     */
    protected $organisationPersons;

    /**
     * Read audit
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\OrganisationReadAudit",
     *     mappedBy="organisation"
     * )
     */
    protected $readAudits;

    /**
     * Organisation user
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser",
     *     mappedBy="organisation"
     * )
     */
    protected $organisationUsers;

    /**
     * Trading name
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\TradingName",
     *     mappedBy="organisation"
     * )
     */
    protected $tradingNames;

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
        $this->disqualifications = new ArrayCollection();
        $this->irfoPartners = new ArrayCollection();
        $this->licences = new ArrayCollection();
        $this->organisationPersons = new ArrayCollection();
        $this->readAudits = new ArrayCollection();
        $this->organisationUsers = new ArrayCollection();
        $this->tradingNames = new ArrayCollection();
    }

    /**
     * Set the allow email
     *
     * @param string $allowEmail new value being set
     *
     * @return Organisation
     */
    public function setAllowEmail($allowEmail)
    {
        $this->allowEmail = $allowEmail;

        return $this;
    }

    /**
     * Get the allow email
     *
     * @return string
     */
    public function getAllowEmail()
    {
        return $this->allowEmail;
    }

    /**
     * Set the company cert seen
     *
     * @param string $companyCertSeen new value being set
     *
     * @return Organisation
     */
    public function setCompanyCertSeen($companyCertSeen)
    {
        $this->companyCertSeen = $companyCertSeen;

        return $this;
    }

    /**
     * Get the company cert seen
     *
     * @return string
     */
    public function getCompanyCertSeen()
    {
        return $this->companyCertSeen;
    }

    /**
     * Set the company or llp no
     *
     * @param string $companyOrLlpNo new value being set
     *
     * @return Organisation
     */
    public function setCompanyOrLlpNo($companyOrLlpNo)
    {
        $this->companyOrLlpNo = $companyOrLlpNo;

        return $this;
    }

    /**
     * Get the company or llp no
     *
     * @return string
     */
    public function getCompanyOrLlpNo()
    {
        return $this->companyOrLlpNo;
    }

    /**
     * Set the confirm share trailer info
     *
     * @param string $confirmShareTrailerInfo new value being set
     *
     * @return Organisation
     */
    public function setConfirmShareTrailerInfo($confirmShareTrailerInfo)
    {
        $this->confirmShareTrailerInfo = $confirmShareTrailerInfo;

        return $this;
    }

    /**
     * Get the confirm share trailer info
     *
     * @return string
     */
    public function getConfirmShareTrailerInfo()
    {
        return $this->confirmShareTrailerInfo;
    }

    /**
     * Set the confirm share vehicle info
     *
     * @param string $confirmShareVehicleInfo new value being set
     *
     * @return Organisation
     */
    public function setConfirmShareVehicleInfo($confirmShareVehicleInfo)
    {
        $this->confirmShareVehicleInfo = $confirmShareVehicleInfo;

        return $this;
    }

    /**
     * Get the confirm share vehicle info
     *
     * @return string
     */
    public function getConfirmShareVehicleInfo()
    {
        return $this->confirmShareVehicleInfo;
    }

    /**
     * Set the contact details
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $contactDetails entity being set as the value
     *
     * @return Organisation
     */
    public function setContactDetails($contactDetails)
    {
        $this->contactDetails = $contactDetails;

        return $this;
    }

    /**
     * Get the contact details
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    /**
     * Set the cpid
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $cpid entity being set as the value
     *
     * @return Organisation
     */
    public function setCpid($cpid)
    {
        $this->cpid = $cpid;

        return $this;
    }

    /**
     * Get the cpid
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getCpid()
    {
        return $this->cpid;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Organisation
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
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Organisation
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
     * Set the irfo contact details
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $irfoContactDetails entity being set as the value
     *
     * @return Organisation
     */
    public function setIrfoContactDetails($irfoContactDetails)
    {
        $this->irfoContactDetails = $irfoContactDetails;

        return $this;
    }

    /**
     * Get the irfo contact details
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     */
    public function getIrfoContactDetails()
    {
        return $this->irfoContactDetails;
    }

    /**
     * Set the irfo nationality
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\Country $irfoNationality entity being set as the value
     *
     * @return Organisation
     */
    public function setIrfoNationality($irfoNationality)
    {
        $this->irfoNationality = $irfoNationality;

        return $this;
    }

    /**
     * Get the irfo nationality
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\Country
     */
    public function getIrfoNationality()
    {
        return $this->irfoNationality;
    }

    /**
     * Set the is irfo
     *
     * @param string $isIrfo new value being set
     *
     * @return Organisation
     */
    public function setIsIrfo($isIrfo)
    {
        $this->isIrfo = $isIrfo;

        return $this;
    }

    /**
     * Get the is irfo
     *
     * @return string
     */
    public function getIsIrfo()
    {
        return $this->isIrfo;
    }

    /**
     * Set the is messaging disabled
     *
     * @param boolean $isMessagingDisabled new value being set
     *
     * @return Organisation
     */
    public function setIsMessagingDisabled($isMessagingDisabled)
    {
        $this->isMessagingDisabled = $isMessagingDisabled;

        return $this;
    }

    /**
     * Get the is messaging disabled
     *
     * @return boolean
     */
    public function getIsMessagingDisabled()
    {
        return $this->isMessagingDisabled;
    }

    /**
     * Set the is messaging file upload enabled
     *
     * @param boolean $isMessagingFileUploadEnabled new value being set
     *
     * @return Organisation
     */
    public function setIsMessagingFileUploadEnabled($isMessagingFileUploadEnabled)
    {
        $this->isMessagingFileUploadEnabled = $isMessagingFileUploadEnabled;

        return $this;
    }

    /**
     * Get the is messaging file upload enabled
     *
     * @return boolean
     */
    public function getIsMessagingFileUploadEnabled()
    {
        return $this->isMessagingFileUploadEnabled;
    }

    /**
     * Set the is unlicensed
     *
     * @param boolean $isUnlicensed new value being set
     *
     * @return Organisation
     */
    public function setIsUnlicensed($isUnlicensed)
    {
        $this->isUnlicensed = $isUnlicensed;

        return $this;
    }

    /**
     * Get the is unlicensed
     *
     * @return boolean
     */
    public function getIsUnlicensed()
    {
        return $this->isUnlicensed;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return Organisation
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
     * Set the lead tc area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea $leadTcArea entity being set as the value
     *
     * @return Organisation
     */
    public function setLeadTcArea($leadTcArea)
    {
        $this->leadTcArea = $leadTcArea;

        return $this;
    }

    /**
     * Get the lead tc area
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     */
    public function getLeadTcArea()
    {
        return $this->leadTcArea;
    }

    /**
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return Organisation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the nature of business
     *
     * @param string $natureOfBusiness new value being set
     *
     * @return Organisation
     */
    public function setNatureOfBusiness($natureOfBusiness)
    {
        $this->natureOfBusiness = $natureOfBusiness;

        return $this;
    }

    /**
     * Get the nature of business
     *
     * @return string
     */
    public function getNatureOfBusiness()
    {
        return $this->natureOfBusiness;
    }

    /**
     * Set the type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $type entity being set as the value
     *
     * @return Organisation
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Organisation
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
     * Set the disqualification
     *
     * @param ArrayCollection $disqualifications collection being set as the value
     *
     * @return Organisation
     */
    public function setDisqualifications($disqualifications)
    {
        $this->disqualifications = $disqualifications;

        return $this;
    }

    /**
     * Get the disqualifications
     *
     * @return ArrayCollection
     */
    public function getDisqualifications()
    {
        return $this->disqualifications;
    }

    /**
     * Add a disqualifications
     *
     * @param ArrayCollection|mixed $disqualifications collection being added
     *
     * @return Organisation
     */
    public function addDisqualifications($disqualifications)
    {
        if ($disqualifications instanceof ArrayCollection) {
            $this->disqualifications = new ArrayCollection(
                array_merge(
                    $this->disqualifications->toArray(),
                    $disqualifications->toArray()
                )
            );
        } elseif (!$this->disqualifications->contains($disqualifications)) {
            $this->disqualifications->add($disqualifications);
        }

        return $this;
    }

    /**
     * Remove a disqualifications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $disqualifications collection being removed
     *
     * @return Organisation
     */
    public function removeDisqualifications($disqualifications)
    {
        if ($this->disqualifications->contains($disqualifications)) {
            $this->disqualifications->removeElement($disqualifications);
        }

        return $this;
    }

    /**
     * Set the irfo partner
     *
     * @param ArrayCollection $irfoPartners collection being set as the value
     *
     * @return Organisation
     */
    public function setIrfoPartners($irfoPartners)
    {
        $this->irfoPartners = $irfoPartners;

        return $this;
    }

    /**
     * Get the irfo partners
     *
     * @return ArrayCollection
     */
    public function getIrfoPartners()
    {
        return $this->irfoPartners;
    }

    /**
     * Add a irfo partners
     *
     * @param ArrayCollection|mixed $irfoPartners collection being added
     *
     * @return Organisation
     */
    public function addIrfoPartners($irfoPartners)
    {
        if ($irfoPartners instanceof ArrayCollection) {
            $this->irfoPartners = new ArrayCollection(
                array_merge(
                    $this->irfoPartners->toArray(),
                    $irfoPartners->toArray()
                )
            );
        } elseif (!$this->irfoPartners->contains($irfoPartners)) {
            $this->irfoPartners->add($irfoPartners);
        }

        return $this;
    }

    /**
     * Remove a irfo partners
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irfoPartners collection being removed
     *
     * @return Organisation
     */
    public function removeIrfoPartners($irfoPartners)
    {
        if ($this->irfoPartners->contains($irfoPartners)) {
            $this->irfoPartners->removeElement($irfoPartners);
        }

        return $this;
    }

    /**
     * Set the licence
     *
     * @param ArrayCollection $licences collection being set as the value
     *
     * @return Organisation
     */
    public function setLicences($licences)
    {
        $this->licences = $licences;

        return $this;
    }

    /**
     * Get the licences
     *
     * @return ArrayCollection
     */
    public function getLicences()
    {
        return $this->licences;
    }

    /**
     * Add a licences
     *
     * @param ArrayCollection|mixed $licences collection being added
     *
     * @return Organisation
     */
    public function addLicences($licences)
    {
        if ($licences instanceof ArrayCollection) {
            $this->licences = new ArrayCollection(
                array_merge(
                    $this->licences->toArray(),
                    $licences->toArray()
                )
            );
        } elseif (!$this->licences->contains($licences)) {
            $this->licences->add($licences);
        }

        return $this;
    }

    /**
     * Remove a licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licences collection being removed
     *
     * @return Organisation
     */
    public function removeLicences($licences)
    {
        if ($this->licences->contains($licences)) {
            $this->licences->removeElement($licences);
        }

        return $this;
    }

    /**
     * Set the organisation person
     *
     * @param ArrayCollection $organisationPersons collection being set as the value
     *
     * @return Organisation
     */
    public function setOrganisationPersons($organisationPersons)
    {
        $this->organisationPersons = $organisationPersons;

        return $this;
    }

    /**
     * Get the organisation persons
     *
     * @return ArrayCollection
     */
    public function getOrganisationPersons()
    {
        return $this->organisationPersons;
    }

    /**
     * Add a organisation persons
     *
     * @param ArrayCollection|mixed $organisationPersons collection being added
     *
     * @return Organisation
     */
    public function addOrganisationPersons($organisationPersons)
    {
        if ($organisationPersons instanceof ArrayCollection) {
            $this->organisationPersons = new ArrayCollection(
                array_merge(
                    $this->organisationPersons->toArray(),
                    $organisationPersons->toArray()
                )
            );
        } elseif (!$this->organisationPersons->contains($organisationPersons)) {
            $this->organisationPersons->add($organisationPersons);
        }

        return $this;
    }

    /**
     * Remove a organisation persons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $organisationPersons collection being removed
     *
     * @return Organisation
     */
    public function removeOrganisationPersons($organisationPersons)
    {
        if ($this->organisationPersons->contains($organisationPersons)) {
            $this->organisationPersons->removeElement($organisationPersons);
        }

        return $this;
    }

    /**
     * Set the read audit
     *
     * @param ArrayCollection $readAudits collection being set as the value
     *
     * @return Organisation
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
     * @return Organisation
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
     * @return Organisation
     */
    public function removeReadAudits($readAudits)
    {
        if ($this->readAudits->contains($readAudits)) {
            $this->readAudits->removeElement($readAudits);
        }

        return $this;
    }

    /**
     * Set the organisation user
     *
     * @param ArrayCollection $organisationUsers collection being set as the value
     *
     * @return Organisation
     */
    public function setOrganisationUsers($organisationUsers)
    {
        $this->organisationUsers = $organisationUsers;

        return $this;
    }

    /**
     * Get the organisation users
     *
     * @return ArrayCollection
     */
    public function getOrganisationUsers()
    {
        return $this->organisationUsers;
    }

    /**
     * Add a organisation users
     *
     * @param ArrayCollection|mixed $organisationUsers collection being added
     *
     * @return Organisation
     */
    public function addOrganisationUsers($organisationUsers)
    {
        if ($organisationUsers instanceof ArrayCollection) {
            $this->organisationUsers = new ArrayCollection(
                array_merge(
                    $this->organisationUsers->toArray(),
                    $organisationUsers->toArray()
                )
            );
        } elseif (!$this->organisationUsers->contains($organisationUsers)) {
            $this->organisationUsers->add($organisationUsers);
        }

        return $this;
    }

    /**
     * Remove a organisation users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $organisationUsers collection being removed
     *
     * @return Organisation
     */
    public function removeOrganisationUsers($organisationUsers)
    {
        if ($this->organisationUsers->contains($organisationUsers)) {
            $this->organisationUsers->removeElement($organisationUsers);
        }

        return $this;
    }

    /**
     * Set the trading name
     *
     * @param ArrayCollection $tradingNames collection being set as the value
     *
     * @return Organisation
     */
    public function setTradingNames($tradingNames)
    {
        $this->tradingNames = $tradingNames;

        return $this;
    }

    /**
     * Get the trading names
     *
     * @return ArrayCollection
     */
    public function getTradingNames()
    {
        return $this->tradingNames;
    }

    /**
     * Add a trading names
     *
     * @param ArrayCollection|mixed $tradingNames collection being added
     *
     * @return Organisation
     */
    public function addTradingNames($tradingNames)
    {
        if ($tradingNames instanceof ArrayCollection) {
            $this->tradingNames = new ArrayCollection(
                array_merge(
                    $this->tradingNames->toArray(),
                    $tradingNames->toArray()
                )
            );
        } elseif (!$this->tradingNames->contains($tradingNames)) {
            $this->tradingNames->add($tradingNames);
        }

        return $this;
    }

    /**
     * Remove a trading names
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tradingNames collection being removed
     *
     * @return Organisation
     */
    public function removeTradingNames($tradingNames)
    {
        if ($this->tradingNames->contains($tradingNames)) {
            $this->tradingNames->removeElement($tradingNames);
        }

        return $this;
    }
}
