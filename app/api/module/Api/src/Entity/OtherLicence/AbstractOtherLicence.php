<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\OtherLicence;

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
 * AbstractOtherLicence Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="other_licence",
 *    indexes={
 *        @ORM\Index(name="ix_other_licence_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_other_licence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_other_licence_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_other_licence_previous_licence_type", columns={"previous_licence_type"}),
 *        @ORM\Index(name="ix_other_licence_role", columns={"role"}),
 *        @ORM\Index(name="ix_other_licence_transport_manager_application_id", columns={"transport_manager_application_id"}),
 *        @ORM\Index(name="ix_other_licence_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_other_licence_transport_manager_licence_id", columns={"transport_manager_licence_id"})
 *    }
 * )
 */
abstract class AbstractOtherLicence implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\Application", fetch="LAZY")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=true)
     */
    protected $application;

    /**
     * Foreign Key to transport_manager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id", nullable=true)
     */
    protected $transportManager;

    /**
     * Foreign Key to transport_manager_licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_licence_id", referencedColumnName="id", nullable=true)
     */
    protected $transportManagerLicence;

    /**
     * Foreign Key to transport_manager_application
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_application_id", referencedColumnName="id", nullable=true)
     */
    protected $transportManagerApplication;

    /**
     * Role
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="role", referencedColumnName="id", nullable=true)
     */
    protected $role;

    /**
     * PreviousLicenceType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="previous_licence_type", referencedColumnName="id", nullable=true)
     */
    protected $previousLicenceType;

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
     * Lic no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="lic_no", length=18, nullable=true)
     */
    protected $licNo;

    /**
     * Holder name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="holder_name", length=90, nullable=true)
     */
    protected $holderName;

    /**
     * Purchase date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="purchase_date", nullable=true)
     */
    protected $purchaseDate;

    /**
     * willSurrender
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="will_surrender", nullable=true)
     */
    protected $willSurrender;

    /**
     * Disqualification date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="disqualification_date", nullable=true)
     */
    protected $disqualificationDate;

    /**
     * Disqualification length
     *
     * @var string
     *
     * @ORM\Column(type="string", name="disqualification_length", length=255, nullable=true)
     */
    protected $disqualificationLength;

    /**
     * Additional information
     *
     * @var string
     *
     * @ORM\Column(type="string", name="additional_information", length=4000, nullable=true)
     */
    protected $additionalInformation;

    /**
     * Operating centres
     *
     * @var string
     *
     * @ORM\Column(type="string", name="operating_centres", length=255, nullable=true)
     */
    protected $operatingCentres;

    /**
     * Total auth vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="total_auth_vehicles", nullable=true)
     */
    protected $totalAuthVehicles;

    /**
     * If on transport manager
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_per_week", nullable=true)
     */
    protected $hoursPerWeek;

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
     * @return OtherLicence
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
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application new value being set
     *
     * @return OtherLicence
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
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager new value being set
     *
     * @return OtherLicence
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
     * Set the transport manager licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence $transportManagerLicence new value being set
     *
     * @return OtherLicence
     */
    public function setTransportManagerLicence($transportManagerLicence)
    {
        $this->transportManagerLicence = $transportManagerLicence;

        return $this;
    }

    /**
     * Get the transport manager licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence     */
    public function getTransportManagerLicence()
    {
        return $this->transportManagerLicence;
    }

    /**
     * Set the transport manager application
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication $transportManagerApplication new value being set
     *
     * @return OtherLicence
     */
    public function setTransportManagerApplication($transportManagerApplication)
    {
        $this->transportManagerApplication = $transportManagerApplication;

        return $this;
    }

    /**
     * Get the transport manager application
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication     */
    public function getTransportManagerApplication()
    {
        return $this->transportManagerApplication;
    }

    /**
     * Set the role
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $role new value being set
     *
     * @return OtherLicence
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the role
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the previous licence type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $previousLicenceType new value being set
     *
     * @return OtherLicence
     */
    public function setPreviousLicenceType($previousLicenceType)
    {
        $this->previousLicenceType = $previousLicenceType;

        return $this;
    }

    /**
     * Get the previous licence type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getPreviousLicenceType()
    {
        return $this->previousLicenceType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return OtherLicence
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
     * @return OtherLicence
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
     * Set the lic no
     *
     * @param string $licNo new value being set
     *
     * @return OtherLicence
     */
    public function setLicNo($licNo)
    {
        $this->licNo = $licNo;

        return $this;
    }

    /**
     * Get the lic no
     *
     * @return string     */
    public function getLicNo()
    {
        return $this->licNo;
    }

    /**
     * Set the holder name
     *
     * @param string $holderName new value being set
     *
     * @return OtherLicence
     */
    public function setHolderName($holderName)
    {
        $this->holderName = $holderName;

        return $this;
    }

    /**
     * Get the holder name
     *
     * @return string     */
    public function getHolderName()
    {
        return $this->holderName;
    }

    /**
     * Set the purchase date
     *
     * @param \DateTime $purchaseDate new value being set
     *
     * @return OtherLicence
     */
    public function setPurchaseDate($purchaseDate)
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    /**
     * Get the purchase date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPurchaseDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->purchaseDate);
        }

        return $this->purchaseDate;
    }

    /**
     * Set the will surrender
     *
     * @param string $willSurrender new value being set
     *
     * @return OtherLicence
     */
    public function setWillSurrender($willSurrender)
    {
        $this->willSurrender = $willSurrender;

        return $this;
    }

    /**
     * Get the will surrender
     *
     * @return string     */
    public function getWillSurrender()
    {
        return $this->willSurrender;
    }

    /**
     * Set the disqualification date
     *
     * @param \DateTime $disqualificationDate new value being set
     *
     * @return OtherLicence
     */
    public function setDisqualificationDate($disqualificationDate)
    {
        $this->disqualificationDate = $disqualificationDate;

        return $this;
    }

    /**
     * Get the disqualification date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDisqualificationDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->disqualificationDate);
        }

        return $this->disqualificationDate;
    }

    /**
     * Set the disqualification length
     *
     * @param string $disqualificationLength new value being set
     *
     * @return OtherLicence
     */
    public function setDisqualificationLength($disqualificationLength)
    {
        $this->disqualificationLength = $disqualificationLength;

        return $this;
    }

    /**
     * Get the disqualification length
     *
     * @return string     */
    public function getDisqualificationLength()
    {
        return $this->disqualificationLength;
    }

    /**
     * Set the additional information
     *
     * @param string $additionalInformation new value being set
     *
     * @return OtherLicence
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
     * Set the operating centres
     *
     * @param string $operatingCentres new value being set
     *
     * @return OtherLicence
     */
    public function setOperatingCentres($operatingCentres)
    {
        $this->operatingCentres = $operatingCentres;

        return $this;
    }

    /**
     * Get the operating centres
     *
     * @return string     */
    public function getOperatingCentres()
    {
        return $this->operatingCentres;
    }

    /**
     * Set the total auth vehicles
     *
     * @param int $totalAuthVehicles new value being set
     *
     * @return OtherLicence
     */
    public function setTotalAuthVehicles($totalAuthVehicles)
    {
        $this->totalAuthVehicles = $totalAuthVehicles;

        return $this;
    }

    /**
     * Get the total auth vehicles
     *
     * @return int     */
    public function getTotalAuthVehicles()
    {
        return $this->totalAuthVehicles;
    }

    /**
     * Set the hours per week
     *
     * @param string $hoursPerWeek new value being set
     *
     * @return OtherLicence
     */
    public function setHoursPerWeek($hoursPerWeek)
    {
        $this->hoursPerWeek = $hoursPerWeek;

        return $this;
    }

    /**
     * Get the hours per week
     *
     * @return string     */
    public function getHoursPerWeek()
    {
        return $this->hoursPerWeek;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return OtherLicence
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
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
