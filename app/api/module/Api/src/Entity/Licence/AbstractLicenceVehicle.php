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
 * AbstractLicenceVehicle Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="licence_vehicle",
 *    indexes={
 *        @ORM\Index(name="ix_licence_vehicle_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_licence_vehicle_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_licence_vehicle_interim_application_id", columns={"interim_application_id"}),
 *        @ORM\Index(name="ix_licence_vehicle_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_licence_vehicle_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_licence_vehicle_vehicle_id", columns={"vehicle_id"}),
 *        @ORM\Index(name="ix_licence_vehicle_vi_action", columns={"vi_action"}),
 *        @ORM\Index(name="uk_licence_vehicle_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_licence_vehicle_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractLicenceVehicle implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to vehicle
     *
     * @var \Dvsa\Olcs\Api\Entity\Vehicle\Vehicle
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Vehicle\Vehicle", fetch="LAZY", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="vehicle_id", referencedColumnName="id")
     */
    protected $vehicle;

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
     * InterimApplication
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\Application", fetch="LAZY")
     * @ORM\JoinColumn(name="interim_application_id", referencedColumnName="id", nullable=true)
     */
    protected $interimApplication;

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
     * Received date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="received_date", nullable=true)
     */
    protected $receivedDate;

    /**
     * Date vehicle removed from licence
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="removal_date", nullable=true)
     */
    protected $removalDate;

    /**
     * Removal letter seed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="removal_letter_seed_date", nullable=true)
     */
    protected $removalLetterSeedDate;

    /**
     * Vi action
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vi_action", length=1, nullable=true)
     */
    protected $viAction;

    /**
     * Warning letter seed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="warning_letter_seed_date", nullable=true)
     */
    protected $warningLetterSeedDate;

    /**
     * Warning letter sent date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="warning_letter_sent_date", nullable=true)
     */
    protected $warningLetterSentDate;

    /**
     * Specified date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="specified_date", nullable=true)
     */
    protected $specifiedDate;

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
     * GoodsDiscs
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc", mappedBy="licenceVehicle")
     * @ORM\OrderBy({"createdOn" = "DESC"})
     */
    protected $goodsDiscs;

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
        $this->goodsDiscs = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return LicenceVehicle
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
     * @return LicenceVehicle
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
     * Set the vehicle
     *
     * @param \Dvsa\Olcs\Api\Entity\Vehicle\Vehicle $vehicle new value being set
     *
     * @return LicenceVehicle
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get the vehicle
     *
     * @return \Dvsa\Olcs\Api\Entity\Vehicle\Vehicle     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application new value being set
     *
     * @return LicenceVehicle
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
     * Set the interim application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $interimApplication new value being set
     *
     * @return LicenceVehicle
     */
    public function setInterimApplication($interimApplication)
    {
        $this->interimApplication = $interimApplication;

        return $this;
    }

    /**
     * Get the interim application
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\Application     */
    public function getInterimApplication()
    {
        return $this->interimApplication;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LicenceVehicle
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
     * @return LicenceVehicle
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
     * Set the received date
     *
     * @param \DateTime $receivedDate new value being set
     *
     * @return LicenceVehicle
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
     * @return \DateTime     */
    public function getReceivedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->receivedDate);
        }

        return $this->receivedDate;
    }

    /**
     * Set the removal date
     *
     * @param \DateTime $removalDate new value being set
     *
     * @return LicenceVehicle
     */
    public function setRemovalDate($removalDate)
    {
        $this->removalDate = $removalDate;

        return $this;
    }

    /**
     * Get the removal date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRemovalDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->removalDate);
        }

        return $this->removalDate;
    }

    /**
     * Set the removal letter seed date
     *
     * @param \DateTime $removalLetterSeedDate new value being set
     *
     * @return LicenceVehicle
     */
    public function setRemovalLetterSeedDate($removalLetterSeedDate)
    {
        $this->removalLetterSeedDate = $removalLetterSeedDate;

        return $this;
    }

    /**
     * Get the removal letter seed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRemovalLetterSeedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->removalLetterSeedDate);
        }

        return $this->removalLetterSeedDate;
    }

    /**
     * Set the vi action
     *
     * @param string $viAction new value being set
     *
     * @return LicenceVehicle
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
     * Set the warning letter seed date
     *
     * @param \DateTime $warningLetterSeedDate new value being set
     *
     * @return LicenceVehicle
     */
    public function setWarningLetterSeedDate($warningLetterSeedDate)
    {
        $this->warningLetterSeedDate = $warningLetterSeedDate;

        return $this;
    }

    /**
     * Get the warning letter seed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getWarningLetterSeedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->warningLetterSeedDate);
        }

        return $this->warningLetterSeedDate;
    }

    /**
     * Set the warning letter sent date
     *
     * @param \DateTime $warningLetterSentDate new value being set
     *
     * @return LicenceVehicle
     */
    public function setWarningLetterSentDate($warningLetterSentDate)
    {
        $this->warningLetterSentDate = $warningLetterSentDate;

        return $this;
    }

    /**
     * Get the warning letter sent date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getWarningLetterSentDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->warningLetterSentDate);
        }

        return $this->warningLetterSentDate;
    }

    /**
     * Set the specified date
     *
     * @param \DateTime $specifiedDate new value being set
     *
     * @return LicenceVehicle
     */
    public function setSpecifiedDate($specifiedDate)
    {
        $this->specifiedDate = $specifiedDate;

        return $this;
    }

    /**
     * Get the specified date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getSpecifiedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->specifiedDate);
        }

        return $this->specifiedDate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LicenceVehicle
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
     * @return LicenceVehicle
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
     * Set the goods discs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $goodsDiscs collection being set as the value
     *
     * @return LicenceVehicle
     */
    public function setGoodsDiscs($goodsDiscs)
    {
        $this->goodsDiscs = $goodsDiscs;

        return $this;
    }

    /**
     * Get the goods discs
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getGoodsDiscs()
    {
        return $this->goodsDiscs;
    }

    /**
     * Add a goods discs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $goodsDiscs collection being added
     *
     * @return LicenceVehicle
     */
    public function addGoodsDiscs($goodsDiscs)
    {
        if ($goodsDiscs instanceof ArrayCollection) {
            $this->goodsDiscs = new ArrayCollection(
                array_merge(
                    $this->goodsDiscs->toArray(),
                    $goodsDiscs->toArray()
                )
            );
        } elseif (!$this->goodsDiscs->contains($goodsDiscs)) {
            $this->goodsDiscs->add($goodsDiscs);
        }

        return $this;
    }

    /**
     * Remove a goods discs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $goodsDiscs collection being removed
     *
     * @return LicenceVehicle
     */
    public function removeGoodsDiscs($goodsDiscs)
    {
        if ($this->goodsDiscs->contains($goodsDiscs)) {
            $this->goodsDiscs->removeElement($goodsDiscs);
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