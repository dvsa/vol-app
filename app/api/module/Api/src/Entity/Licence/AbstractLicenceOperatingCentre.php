<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Licence;

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
 * AbstractLicenceOperatingCentre Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="licence_operating_centre",
 *    indexes={
 *        @ORM\Index(name="ix_licence_operating_centre_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_licence_operating_centre_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_licence_operating_centre_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_licence_operating_centre_operating_centre_id", columns={"operating_centre_id"}),
 *        @ORM\Index(name="ix_licence_operating_centre_s4_id", columns={"s4_id"}),
 *        @ORM\Index(name="uk_licence_operating_centre_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_licence_operating_centre_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractLicenceOperatingCentre implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id")
     */
    protected $licence;

    /**
     * Foreign Key to operating_centre
     *
     * @var \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre", fetch="LAZY")
     * @ORM\JoinColumn(name="operating_centre_id", referencedColumnName="id")
     */
    protected $operatingCentre;

    /**
     * Foreign Key to s4
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\S4
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\S4", fetch="LAZY")
     * @ORM\JoinColumn(name="s4_id", referencedColumnName="id", nullable=true)
     */
    protected $s4;

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
     * An advert has been placed in a local approved publication to notify of op centre changes
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="ad_placed", nullable=false)
     */
    protected $adPlaced = 0;

    /**
     * Publication advertisement placed in.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="ad_placed_in", length=70, nullable=true)
     */
    protected $adPlacedIn;

    /**
     * Date advert placed.  Must be x number of days before application can be granted.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="ad_placed_date", nullable=true)
     */
    protected $adPlacedDate;

    /**
     * Land is owned or permission granted on sitte for parking.
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="permission", nullable=false)
     */
    protected $permission = 0;

    /**
     * Number of trailers required to be at the operating centre at one time
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="no_of_trailers_required", nullable=true)
     */
    protected $noOfTrailersRequired;

    /**
     * Number of vehicles required to be at the oc at one time
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="no_of_vehicles_required", nullable=true)
     */
    protected $noOfVehiclesRequired;

    /**
     * No of vehicles possessed.  Not related to required directly as vehicles are often off site.
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="no_of_vehicles_possessed", nullable=true)
     */
    protected $noOfVehiclesPossessed;

    /**
     * No of trailers possessed. Not directly related to number required as some will be off site or at other op centres.
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="no_of_trailers_possessed", nullable=true)
     */
    protected $noOfTrailersPossessed;

    /**
     * Flag to send changes to mobile conpliance/vehicle inspectorate system
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vi_action", length=1, nullable=true)
     */
    protected $viAction;

    /**
     * Is an interim licence.
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_interim", nullable=true)
     */
    protected $isInterim;

    /**
     * Advertisement was placed in an appropriate publication.  i.e. local with wide enough readership.
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="publication_appropriate", nullable=true)
     */
    protected $publicationAppropriate;

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
     * @return LicenceOperatingCentre
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
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the operating centre
     *
     * @param \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre $operatingCentre new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setOperatingCentre($operatingCentre)
    {
        $this->operatingCentre = $operatingCentre;

        return $this;
    }

    /**
     * Get the operating centre
     *
     * @return \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * Set the s4
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\S4 $s4 new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setS4($s4)
    {
        $this->s4 = $s4;

        return $this;
    }

    /**
     * Get the s4
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\S4     */
    public function getS4()
    {
        return $this->s4;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LicenceOperatingCentre
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
     * @return LicenceOperatingCentre
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
     * Set the ad placed
     *
     * @param int $adPlaced new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setAdPlaced($adPlaced)
    {
        $this->adPlaced = $adPlaced;

        return $this;
    }

    /**
     * Get the ad placed
     *
     * @return int     */
    public function getAdPlaced()
    {
        return $this->adPlaced;
    }

    /**
     * Set the ad placed in
     *
     * @param string $adPlacedIn new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setAdPlacedIn($adPlacedIn)
    {
        $this->adPlacedIn = $adPlacedIn;

        return $this;
    }

    /**
     * Get the ad placed in
     *
     * @return string     */
    public function getAdPlacedIn()
    {
        return $this->adPlacedIn;
    }

    /**
     * Set the ad placed date
     *
     * @param \DateTime $adPlacedDate new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setAdPlacedDate($adPlacedDate)
    {
        $this->adPlacedDate = $adPlacedDate;

        return $this;
    }

    /**
     * Get the ad placed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getAdPlacedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->adPlacedDate);
        }

        return $this->adPlacedDate;
    }

    /**
     * Set the permission
     *
     * @param string $permission new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get the permission
     *
     * @return string     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set the no of trailers required
     *
     * @param int $noOfTrailersRequired new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setNoOfTrailersRequired($noOfTrailersRequired)
    {
        $this->noOfTrailersRequired = $noOfTrailersRequired;

        return $this;
    }

    /**
     * Get the no of trailers required
     *
     * @return int     */
    public function getNoOfTrailersRequired()
    {
        return $this->noOfTrailersRequired;
    }

    /**
     * Set the no of vehicles required
     *
     * @param int $noOfVehiclesRequired new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setNoOfVehiclesRequired($noOfVehiclesRequired)
    {
        $this->noOfVehiclesRequired = $noOfVehiclesRequired;

        return $this;
    }

    /**
     * Get the no of vehicles required
     *
     * @return int     */
    public function getNoOfVehiclesRequired()
    {
        return $this->noOfVehiclesRequired;
    }

    /**
     * Set the no of vehicles possessed
     *
     * @param int $noOfVehiclesPossessed new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setNoOfVehiclesPossessed($noOfVehiclesPossessed)
    {
        $this->noOfVehiclesPossessed = $noOfVehiclesPossessed;

        return $this;
    }

    /**
     * Get the no of vehicles possessed
     *
     * @return int     */
    public function getNoOfVehiclesPossessed()
    {
        return $this->noOfVehiclesPossessed;
    }

    /**
     * Set the no of trailers possessed
     *
     * @param int $noOfTrailersPossessed new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setNoOfTrailersPossessed($noOfTrailersPossessed)
    {
        $this->noOfTrailersPossessed = $noOfTrailersPossessed;

        return $this;
    }

    /**
     * Get the no of trailers possessed
     *
     * @return int     */
    public function getNoOfTrailersPossessed()
    {
        return $this->noOfTrailersPossessed;
    }

    /**
     * Set the vi action
     *
     * @param string $viAction new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setViAction($viAction)
    {
        $this->viAction = $viAction;

        return $this;
    }

    /**
     * Get the vi action
     *
     * @return string     */
    public function getViAction()
    {
        return $this->viAction;
    }

    /**
     * Set the is interim
     *
     * @param string $isInterim new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setIsInterim($isInterim)
    {
        $this->isInterim = $isInterim;

        return $this;
    }

    /**
     * Get the is interim
     *
     * @return string     */
    public function getIsInterim()
    {
        return $this->isInterim;
    }

    /**
     * Set the publication appropriate
     *
     * @param string $publicationAppropriate new value being set
     *
     * @return LicenceOperatingCentre
     */
    public function setPublicationAppropriate($publicationAppropriate)
    {
        $this->publicationAppropriate = $publicationAppropriate;

        return $this;
    }

    /**
     * Get the publication appropriate
     *
     * @return string     */
    public function getPublicationAppropriate()
    {
        return $this->publicationAppropriate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LicenceOperatingCentre
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
     * @return LicenceOperatingCentre
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
