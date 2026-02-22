<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Vehicle;

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
 * AbstractVehicle Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="vehicle",
 *    indexes={
 *        @ORM\Index(name="ix_vehicle_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_vehicle_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_vehicle_vi_action", columns={"vi_action"}),
 *        @ORM\Index(name="ix_vehicle_vrm", columns={"vrm"}),
 *        @ORM\Index(name="uk_vehicle_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_vehicle_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractVehicle implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Nullable for PSVs
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vrm", length=20, nullable=true)
     */
    protected $vrm;

    /**
     * Weight in Kg
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="plated_weight", nullable=true)
     */
    protected $platedWeight;

    /**
     * psv only
     *
     * @var string
     *
     * @ORM\Column(type="string", name="certificate_no", length=50, nullable=true)
     */
    protected $certificateNo;

    /**
     * Flag to send vehicle data to mobile compliance system
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vi_action", length=1, nullable=true)
     */
    protected $viAction;

    /**
     * section 26 applied to vehicle.  Section 26 is where a licence is revoked, suspended or curtailed for a period of time.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="section_26", nullable=false, options={"default": 0})
     */
    protected $section26 = 0;

    /**
     * Vehicle is on curtailed licence, i.e. reduced authorisation
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="section_26_curtail", nullable=false, options={"default": 0})
     */
    protected $section26Curtail = 0;

    /**
     * Vehicle is on revoked licence.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="section_26_revoked", nullable=false, options={"default": 0})
     */
    protected $section26Revoked = 0;

    /**
     * Vehicle is on suspended licence.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="section_26_suspend", nullable=false, options={"default": 0})
     */
    protected $section26Suspend = 0;

    /**
     * For small PSV vehicles the make and model are recorded.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="make_model", length=100, nullable=true)
     */
    protected $makeModel;

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
     * LicenceVehicles
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle", mappedBy="vehicle")
     */
    protected $licenceVehicles;

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
        $this->licenceVehicles = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Vehicle
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Vehicle
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
     * @return Vehicle
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
     * Set the vrm
     *
     * @param string $vrm new value being set
     *
     * @return Vehicle
     */
    public function setVrm($vrm)
    {
        $this->vrm = $vrm;

        return $this;
    }

    /**
     * Get the vrm
     *
     * @return string     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * Set the plated weight
     *
     * @param int $platedWeight new value being set
     *
     * @return Vehicle
     */
    public function setPlatedWeight($platedWeight)
    {
        $this->platedWeight = $platedWeight;

        return $this;
    }

    /**
     * Get the plated weight
     *
     * @return int     */
    public function getPlatedWeight()
    {
        return $this->platedWeight;
    }

    /**
     * Set the certificate no
     *
     * @param string $certificateNo new value being set
     *
     * @return Vehicle
     */
    public function setCertificateNo($certificateNo)
    {
        $this->certificateNo = $certificateNo;

        return $this;
    }

    /**
     * Get the certificate no
     *
     * @return string     */
    public function getCertificateNo()
    {
        return $this->certificateNo;
    }

    /**
     * Set the vi action
     *
     * @param string $viAction new value being set
     *
     * @return Vehicle
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
     * Set the section26
     *
     * @param bool $section26 new value being set
     *
     * @return Vehicle
     */
    public function setSection26($section26)
    {
        $this->section26 = $section26;

        return $this;
    }

    /**
     * Get the section26
     *
     * @return bool     */
    public function getSection26()
    {
        return $this->section26;
    }

    /**
     * Set the section26 curtail
     *
     * @param bool $section26Curtail new value being set
     *
     * @return Vehicle
     */
    public function setSection26Curtail($section26Curtail)
    {
        $this->section26Curtail = $section26Curtail;

        return $this;
    }

    /**
     * Get the section26 curtail
     *
     * @return bool     */
    public function getSection26Curtail()
    {
        return $this->section26Curtail;
    }

    /**
     * Set the section26 revoked
     *
     * @param bool $section26Revoked new value being set
     *
     * @return Vehicle
     */
    public function setSection26Revoked($section26Revoked)
    {
        $this->section26Revoked = $section26Revoked;

        return $this;
    }

    /**
     * Get the section26 revoked
     *
     * @return bool     */
    public function getSection26Revoked()
    {
        return $this->section26Revoked;
    }

    /**
     * Set the section26 suspend
     *
     * @param bool $section26Suspend new value being set
     *
     * @return Vehicle
     */
    public function setSection26Suspend($section26Suspend)
    {
        $this->section26Suspend = $section26Suspend;

        return $this;
    }

    /**
     * Get the section26 suspend
     *
     * @return bool     */
    public function getSection26Suspend()
    {
        return $this->section26Suspend;
    }

    /**
     * Set the make model
     *
     * @param string $makeModel new value being set
     *
     * @return Vehicle
     */
    public function setMakeModel($makeModel)
    {
        $this->makeModel = $makeModel;

        return $this;
    }

    /**
     * Get the make model
     *
     * @return string     */
    public function getMakeModel()
    {
        return $this->makeModel;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Vehicle
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
     * @return Vehicle
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
     * Set the licence vehicles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licenceVehicles collection being set as the value
     *
     * @return Vehicle
     */
    public function setLicenceVehicles($licenceVehicles)
    {
        $this->licenceVehicles = $licenceVehicles;

        return $this;
    }

    /**
     * Get the licence vehicles
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getLicenceVehicles()
    {
        return $this->licenceVehicles;
    }

    /**
     * Add a licence vehicles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $licenceVehicles collection being added
     *
     * @return Vehicle
     */
    public function addLicenceVehicles($licenceVehicles)
    {
        if ($licenceVehicles instanceof ArrayCollection) {
            $this->licenceVehicles = new ArrayCollection(
                array_merge(
                    $this->licenceVehicles->toArray(),
                    $licenceVehicles->toArray()
                )
            );
        } elseif (!$this->licenceVehicles->contains($licenceVehicles)) {
            $this->licenceVehicles->add($licenceVehicles);
        }

        return $this;
    }

    /**
     * Remove a licence vehicles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $licenceVehicles collection being removed
     *
     * @return Vehicle
     */
    public function removeLicenceVehicles($licenceVehicles)
    {
        if ($this->licenceVehicles->contains($licenceVehicles)) {
            $this->licenceVehicles->removeElement($licenceVehicles);
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
