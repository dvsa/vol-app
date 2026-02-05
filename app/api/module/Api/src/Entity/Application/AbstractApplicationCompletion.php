<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Application;

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
 * AbstractApplicationCompletion Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="application_completion",
 *    indexes={
 *        @ORM\Index(name="ix_application_completion_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_application_completion_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_application_completion_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="uk_application_completion_application_id", columns={"application_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_application_completion_application_id", columns={"application_id"})
 *    }
 * )
 */
abstract class AbstractApplicationCompletion implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\Application", fetch="LAZY")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
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
     * Type of licence status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="type_of_licence_status", nullable=true)
     */
    protected $typeOfLicenceStatus;

    /**
     * Business type status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="business_type_status", nullable=true)
     */
    protected $businessTypeStatus;

    /**
     * Business details status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="business_details_status", nullable=true)
     */
    protected $businessDetailsStatus;

    /**
     * Addresses status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="addresses_status", nullable=true)
     */
    protected $addressesStatus;

    /**
     * People status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="people_status", nullable=true)
     */
    protected $peopleStatus;

    /**
     * Taxi phv status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="taxi_phv_status", nullable=true)
     */
    protected $taxiPhvStatus;

    /**
     * Operating centres status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="operating_centres_status", nullable=true)
     */
    protected $operatingCentresStatus;

    /**
     * Financial evidence status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="financial_evidence_status", nullable=true)
     */
    protected $financialEvidenceStatus;

    /**
     * Transport managers status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="transport_managers_status", nullable=true)
     */
    protected $transportManagersStatus;

    /**
     * Vehicles status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="vehicles_status", nullable=true)
     */
    protected $vehiclesStatus;

    /**
     * Vehicles psv status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="vehicles_psv_status", nullable=true)
     */
    protected $vehiclesPsvStatus;

    /**
     * Vehicles size status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="vehicles_size_status", nullable=true)
     */
    protected $vehiclesSizeStatus;

    /**
     * Psv operate small status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="psv_operate_small_status", nullable=true)
     */
    protected $psvOperateSmallStatus;

    /**
     * Psv operate large status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="psv_operate_large_status", nullable=true)
     */
    protected $psvOperateLargeStatus;

    /**
     * Psv small conditions status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="psv_small_conditions_status", nullable=true)
     */
    protected $psvSmallConditionsStatus;

    /**
     * Psv operate novelty status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="psv_operate_novelty_status", nullable=true)
     */
    protected $psvOperateNoveltyStatus;

    /**
     * Psv small part written status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="psv_small_part_written_status", nullable=true)
     */
    protected $psvSmallPartWrittenStatus;

    /**
     * Psv documentary evidence small status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="psv_documentary_evidence_small_status", nullable=true)
     */
    protected $psvDocumentaryEvidenceSmallStatus;

    /**
     * Psv documentary evidence large status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="psv_documentary_evidence_large_status", nullable=true)
     */
    protected $psvDocumentaryEvidenceLargeStatus;

    /**
     * Psv main occupation undertakings status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="psv_main_occupation_undertakings_status", nullable=true)
     */
    protected $psvMainOccupationUndertakingsStatus;

    /**
     * Vehicles declarations status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="vehicles_declarations_status", nullable=true)
     */
    protected $vehiclesDeclarationsStatus;

    /**
     * Discs status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="discs_status", nullable=true)
     */
    protected $discsStatus;

    /**
     * Community licences status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="community_licences_status", nullable=true)
     */
    protected $communityLicencesStatus;

    /**
     * Safety status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="safety_status", nullable=true)
     */
    protected $safetyStatus;

    /**
     * Conditions undertakings status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="conditions_undertakings_status", nullable=true)
     */
    protected $conditionsUndertakingsStatus;

    /**
     * Financial history status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="financial_history_status", nullable=true)
     */
    protected $financialHistoryStatus;

    /**
     * Licence history status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="licence_history_status", nullable=true)
     */
    protected $licenceHistoryStatus;

    /**
     * Convictions penalties status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="convictions_penalties_status", nullable=true)
     */
    protected $convictionsPenaltiesStatus;

    /**
     * Undertakings status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="undertakings_status", nullable=true)
     */
    protected $undertakingsStatus;

    /**
     * Declarations internal status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="declarations_internal_status", nullable=true)
     */
    protected $declarationsInternalStatus;

    /**
     * Last section
     *
     * @var string
     *
     * @ORM\Column(type="string", name="last_section", length=255, nullable=true)
     */
    protected $lastSection;

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
     * @return ApplicationCompletion
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
     * @return ApplicationCompletion
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return ApplicationCompletion
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
     * @return ApplicationCompletion
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
     * Set the type of licence status
     *
     * @param int $typeOfLicenceStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setTypeOfLicenceStatus($typeOfLicenceStatus)
    {
        $this->typeOfLicenceStatus = $typeOfLicenceStatus;

        return $this;
    }

    /**
     * Get the type of licence status
     *
     * @return int     */
    public function getTypeOfLicenceStatus()
    {
        return $this->typeOfLicenceStatus;
    }

    /**
     * Set the business type status
     *
     * @param int $businessTypeStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setBusinessTypeStatus($businessTypeStatus)
    {
        $this->businessTypeStatus = $businessTypeStatus;

        return $this;
    }

    /**
     * Get the business type status
     *
     * @return int     */
    public function getBusinessTypeStatus()
    {
        return $this->businessTypeStatus;
    }

    /**
     * Set the business details status
     *
     * @param int $businessDetailsStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setBusinessDetailsStatus($businessDetailsStatus)
    {
        $this->businessDetailsStatus = $businessDetailsStatus;

        return $this;
    }

    /**
     * Get the business details status
     *
     * @return int     */
    public function getBusinessDetailsStatus()
    {
        return $this->businessDetailsStatus;
    }

    /**
     * Set the addresses status
     *
     * @param int $addressesStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setAddressesStatus($addressesStatus)
    {
        $this->addressesStatus = $addressesStatus;

        return $this;
    }

    /**
     * Get the addresses status
     *
     * @return int     */
    public function getAddressesStatus()
    {
        return $this->addressesStatus;
    }

    /**
     * Set the people status
     *
     * @param int $peopleStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setPeopleStatus($peopleStatus)
    {
        $this->peopleStatus = $peopleStatus;

        return $this;
    }

    /**
     * Get the people status
     *
     * @return int     */
    public function getPeopleStatus()
    {
        return $this->peopleStatus;
    }

    /**
     * Set the taxi phv status
     *
     * @param int $taxiPhvStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setTaxiPhvStatus($taxiPhvStatus)
    {
        $this->taxiPhvStatus = $taxiPhvStatus;

        return $this;
    }

    /**
     * Get the taxi phv status
     *
     * @return int     */
    public function getTaxiPhvStatus()
    {
        return $this->taxiPhvStatus;
    }

    /**
     * Set the operating centres status
     *
     * @param int $operatingCentresStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setOperatingCentresStatus($operatingCentresStatus)
    {
        $this->operatingCentresStatus = $operatingCentresStatus;

        return $this;
    }

    /**
     * Get the operating centres status
     *
     * @return int     */
    public function getOperatingCentresStatus()
    {
        return $this->operatingCentresStatus;
    }

    /**
     * Set the financial evidence status
     *
     * @param int $financialEvidenceStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setFinancialEvidenceStatus($financialEvidenceStatus)
    {
        $this->financialEvidenceStatus = $financialEvidenceStatus;

        return $this;
    }

    /**
     * Get the financial evidence status
     *
     * @return int     */
    public function getFinancialEvidenceStatus()
    {
        return $this->financialEvidenceStatus;
    }

    /**
     * Set the transport managers status
     *
     * @param int $transportManagersStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setTransportManagersStatus($transportManagersStatus)
    {
        $this->transportManagersStatus = $transportManagersStatus;

        return $this;
    }

    /**
     * Get the transport managers status
     *
     * @return int     */
    public function getTransportManagersStatus()
    {
        return $this->transportManagersStatus;
    }

    /**
     * Set the vehicles status
     *
     * @param int $vehiclesStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setVehiclesStatus($vehiclesStatus)
    {
        $this->vehiclesStatus = $vehiclesStatus;

        return $this;
    }

    /**
     * Get the vehicles status
     *
     * @return int     */
    public function getVehiclesStatus()
    {
        return $this->vehiclesStatus;
    }

    /**
     * Set the vehicles psv status
     *
     * @param int $vehiclesPsvStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setVehiclesPsvStatus($vehiclesPsvStatus)
    {
        $this->vehiclesPsvStatus = $vehiclesPsvStatus;

        return $this;
    }

    /**
     * Get the vehicles psv status
     *
     * @return int     */
    public function getVehiclesPsvStatus()
    {
        return $this->vehiclesPsvStatus;
    }

    /**
     * Set the vehicles size status
     *
     * @param int $vehiclesSizeStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setVehiclesSizeStatus($vehiclesSizeStatus)
    {
        $this->vehiclesSizeStatus = $vehiclesSizeStatus;

        return $this;
    }

    /**
     * Get the vehicles size status
     *
     * @return int     */
    public function getVehiclesSizeStatus()
    {
        return $this->vehiclesSizeStatus;
    }

    /**
     * Set the psv operate small status
     *
     * @param int $psvOperateSmallStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setPsvOperateSmallStatus($psvOperateSmallStatus)
    {
        $this->psvOperateSmallStatus = $psvOperateSmallStatus;

        return $this;
    }

    /**
     * Get the psv operate small status
     *
     * @return int     */
    public function getPsvOperateSmallStatus()
    {
        return $this->psvOperateSmallStatus;
    }

    /**
     * Set the psv operate large status
     *
     * @param int $psvOperateLargeStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setPsvOperateLargeStatus($psvOperateLargeStatus)
    {
        $this->psvOperateLargeStatus = $psvOperateLargeStatus;

        return $this;
    }

    /**
     * Get the psv operate large status
     *
     * @return int     */
    public function getPsvOperateLargeStatus()
    {
        return $this->psvOperateLargeStatus;
    }

    /**
     * Set the psv small conditions status
     *
     * @param int $psvSmallConditionsStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setPsvSmallConditionsStatus($psvSmallConditionsStatus)
    {
        $this->psvSmallConditionsStatus = $psvSmallConditionsStatus;

        return $this;
    }

    /**
     * Get the psv small conditions status
     *
     * @return int     */
    public function getPsvSmallConditionsStatus()
    {
        return $this->psvSmallConditionsStatus;
    }

    /**
     * Set the psv operate novelty status
     *
     * @param int $psvOperateNoveltyStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setPsvOperateNoveltyStatus($psvOperateNoveltyStatus)
    {
        $this->psvOperateNoveltyStatus = $psvOperateNoveltyStatus;

        return $this;
    }

    /**
     * Get the psv operate novelty status
     *
     * @return int     */
    public function getPsvOperateNoveltyStatus()
    {
        return $this->psvOperateNoveltyStatus;
    }

    /**
     * Set the psv small part written status
     *
     * @param int $psvSmallPartWrittenStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setPsvSmallPartWrittenStatus($psvSmallPartWrittenStatus)
    {
        $this->psvSmallPartWrittenStatus = $psvSmallPartWrittenStatus;

        return $this;
    }

    /**
     * Get the psv small part written status
     *
     * @return int     */
    public function getPsvSmallPartWrittenStatus()
    {
        return $this->psvSmallPartWrittenStatus;
    }

    /**
     * Set the psv documentary evidence small status
     *
     * @param int $psvDocumentaryEvidenceSmallStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setPsvDocumentaryEvidenceSmallStatus($psvDocumentaryEvidenceSmallStatus)
    {
        $this->psvDocumentaryEvidenceSmallStatus = $psvDocumentaryEvidenceSmallStatus;

        return $this;
    }

    /**
     * Get the psv documentary evidence small status
     *
     * @return int     */
    public function getPsvDocumentaryEvidenceSmallStatus()
    {
        return $this->psvDocumentaryEvidenceSmallStatus;
    }

    /**
     * Set the psv documentary evidence large status
     *
     * @param int $psvDocumentaryEvidenceLargeStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setPsvDocumentaryEvidenceLargeStatus($psvDocumentaryEvidenceLargeStatus)
    {
        $this->psvDocumentaryEvidenceLargeStatus = $psvDocumentaryEvidenceLargeStatus;

        return $this;
    }

    /**
     * Get the psv documentary evidence large status
     *
     * @return int     */
    public function getPsvDocumentaryEvidenceLargeStatus()
    {
        return $this->psvDocumentaryEvidenceLargeStatus;
    }

    /**
     * Set the psv main occupation undertakings status
     *
     * @param int $psvMainOccupationUndertakingsStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setPsvMainOccupationUndertakingsStatus($psvMainOccupationUndertakingsStatus)
    {
        $this->psvMainOccupationUndertakingsStatus = $psvMainOccupationUndertakingsStatus;

        return $this;
    }

    /**
     * Get the psv main occupation undertakings status
     *
     * @return int     */
    public function getPsvMainOccupationUndertakingsStatus()
    {
        return $this->psvMainOccupationUndertakingsStatus;
    }

    /**
     * Set the vehicles declarations status
     *
     * @param int $vehiclesDeclarationsStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setVehiclesDeclarationsStatus($vehiclesDeclarationsStatus)
    {
        $this->vehiclesDeclarationsStatus = $vehiclesDeclarationsStatus;

        return $this;
    }

    /**
     * Get the vehicles declarations status
     *
     * @return int     */
    public function getVehiclesDeclarationsStatus()
    {
        return $this->vehiclesDeclarationsStatus;
    }

    /**
     * Set the discs status
     *
     * @param int $discsStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setDiscsStatus($discsStatus)
    {
        $this->discsStatus = $discsStatus;

        return $this;
    }

    /**
     * Get the discs status
     *
     * @return int     */
    public function getDiscsStatus()
    {
        return $this->discsStatus;
    }

    /**
     * Set the community licences status
     *
     * @param int $communityLicencesStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setCommunityLicencesStatus($communityLicencesStatus)
    {
        $this->communityLicencesStatus = $communityLicencesStatus;

        return $this;
    }

    /**
     * Get the community licences status
     *
     * @return int     */
    public function getCommunityLicencesStatus()
    {
        return $this->communityLicencesStatus;
    }

    /**
     * Set the safety status
     *
     * @param int $safetyStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setSafetyStatus($safetyStatus)
    {
        $this->safetyStatus = $safetyStatus;

        return $this;
    }

    /**
     * Get the safety status
     *
     * @return int     */
    public function getSafetyStatus()
    {
        return $this->safetyStatus;
    }

    /**
     * Set the conditions undertakings status
     *
     * @param int $conditionsUndertakingsStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setConditionsUndertakingsStatus($conditionsUndertakingsStatus)
    {
        $this->conditionsUndertakingsStatus = $conditionsUndertakingsStatus;

        return $this;
    }

    /**
     * Get the conditions undertakings status
     *
     * @return int     */
    public function getConditionsUndertakingsStatus()
    {
        return $this->conditionsUndertakingsStatus;
    }

    /**
     * Set the financial history status
     *
     * @param int $financialHistoryStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setFinancialHistoryStatus($financialHistoryStatus)
    {
        $this->financialHistoryStatus = $financialHistoryStatus;

        return $this;
    }

    /**
     * Get the financial history status
     *
     * @return int     */
    public function getFinancialHistoryStatus()
    {
        return $this->financialHistoryStatus;
    }

    /**
     * Set the licence history status
     *
     * @param int $licenceHistoryStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setLicenceHistoryStatus($licenceHistoryStatus)
    {
        $this->licenceHistoryStatus = $licenceHistoryStatus;

        return $this;
    }

    /**
     * Get the licence history status
     *
     * @return int     */
    public function getLicenceHistoryStatus()
    {
        return $this->licenceHistoryStatus;
    }

    /**
     * Set the convictions penalties status
     *
     * @param int $convictionsPenaltiesStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setConvictionsPenaltiesStatus($convictionsPenaltiesStatus)
    {
        $this->convictionsPenaltiesStatus = $convictionsPenaltiesStatus;

        return $this;
    }

    /**
     * Get the convictions penalties status
     *
     * @return int     */
    public function getConvictionsPenaltiesStatus()
    {
        return $this->convictionsPenaltiesStatus;
    }

    /**
     * Set the undertakings status
     *
     * @param int $undertakingsStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setUndertakingsStatus($undertakingsStatus)
    {
        $this->undertakingsStatus = $undertakingsStatus;

        return $this;
    }

    /**
     * Get the undertakings status
     *
     * @return int     */
    public function getUndertakingsStatus()
    {
        return $this->undertakingsStatus;
    }

    /**
     * Set the declarations internal status
     *
     * @param int $declarationsInternalStatus new value being set
     *
     * @return ApplicationCompletion
     */
    public function setDeclarationsInternalStatus($declarationsInternalStatus)
    {
        $this->declarationsInternalStatus = $declarationsInternalStatus;

        return $this;
    }

    /**
     * Get the declarations internal status
     *
     * @return int     */
    public function getDeclarationsInternalStatus()
    {
        return $this->declarationsInternalStatus;
    }

    /**
     * Set the last section
     *
     * @param string $lastSection new value being set
     *
     * @return ApplicationCompletion
     */
    public function setLastSection($lastSection)
    {
        $this->lastSection = $lastSection;

        return $this;
    }

    /**
     * Get the last section
     *
     * @return string     */
    public function getLastSection()
    {
        return $this->lastSection;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ApplicationCompletion
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
