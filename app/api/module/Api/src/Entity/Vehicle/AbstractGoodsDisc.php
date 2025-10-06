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
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractGoodsDisc Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="goods_disc",
 *    indexes={
 *        @ORM\Index(name="ix_goods_disc_ceased_date", columns={"ceased_date"}),
 *        @ORM\Index(name="ix_goods_disc_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_goods_disc_issued_date", columns={"issued_date"}),
 *        @ORM\Index(name="ix_goods_disc_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_goods_disc_licence_vehicle_id", columns={"licence_vehicle_id"}),
 *        @ORM\Index(name="uk_goods_disc_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_goods_disc_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractGoodsDisc implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to licence_vehicle
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_vehicle_id", referencedColumnName="id")
     */
    protected $licenceVehicle;

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
     * Disc no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="disc_no", length=50, nullable=true)
     */
    protected $discNo;

    /**
     * Issued date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="issued_date", nullable=true)
     */
    protected $issuedDate;

    /**
     * Ceased date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="ceased_date", nullable=true)
     */
    protected $ceasedDate;

    /**
     * isCopy
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_copy", nullable=false, options={"default": 0})
     */
    protected $isCopy = 0;

    /**
     * isInterim
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_interim", nullable=false, options={"default": 0})
     */
    protected $isInterim = 0;

    /**
     * reprintRequired
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="reprint_required", nullable=false, options={"default": 0})
     */
    protected $reprintRequired = 0;

    /**
     * isPrinting
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_printing", nullable=false, options={"default": 0})
     */
    protected $isPrinting = 0;

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
     * @return GoodsDisc
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
     * Set the licence vehicle
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle $licenceVehicle new value being set
     *
     * @return GoodsDisc
     */
    public function setLicenceVehicle($licenceVehicle)
    {
        $this->licenceVehicle = $licenceVehicle;

        return $this;
    }

    /**
     * Get the licence vehicle
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle     */
    public function getLicenceVehicle()
    {
        return $this->licenceVehicle;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return GoodsDisc
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
     * @return GoodsDisc
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
     * Set the disc no
     *
     * @param string $discNo new value being set
     *
     * @return GoodsDisc
     */
    public function setDiscNo($discNo)
    {
        $this->discNo = $discNo;

        return $this;
    }

    /**
     * Get the disc no
     *
     * @return string     */
    public function getDiscNo()
    {
        return $this->discNo;
    }

    /**
     * Set the issued date
     *
     * @param \DateTime $issuedDate new value being set
     *
     * @return GoodsDisc
     */
    public function setIssuedDate($issuedDate)
    {
        $this->issuedDate = $issuedDate;

        return $this;
    }

    /**
     * Get the issued date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getIssuedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->issuedDate);
        }

        return $this->issuedDate;
    }

    /**
     * Set the ceased date
     *
     * @param \DateTime $ceasedDate new value being set
     *
     * @return GoodsDisc
     */
    public function setCeasedDate($ceasedDate)
    {
        $this->ceasedDate = $ceasedDate;

        return $this;
    }

    /**
     * Get the ceased date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getCeasedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->ceasedDate);
        }

        return $this->ceasedDate;
    }

    /**
     * Set the is copy
     *
     * @param string $isCopy new value being set
     *
     * @return GoodsDisc
     */
    public function setIsCopy($isCopy)
    {
        $this->isCopy = $isCopy;

        return $this;
    }

    /**
     * Get the is copy
     *
     * @return string     */
    public function getIsCopy()
    {
        return $this->isCopy;
    }

    /**
     * Set the is interim
     *
     * @param string $isInterim new value being set
     *
     * @return GoodsDisc
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
     * Set the reprint required
     *
     * @param string $reprintRequired new value being set
     *
     * @return GoodsDisc
     */
    public function setReprintRequired($reprintRequired)
    {
        $this->reprintRequired = $reprintRequired;

        return $this;
    }

    /**
     * Get the reprint required
     *
     * @return string     */
    public function getReprintRequired()
    {
        return $this->reprintRequired;
    }

    /**
     * Set the is printing
     *
     * @param string $isPrinting new value being set
     *
     * @return GoodsDisc
     */
    public function setIsPrinting($isPrinting)
    {
        $this->isPrinting = $isPrinting;

        return $this;
    }

    /**
     * Get the is printing
     *
     * @return string     */
    public function getIsPrinting()
    {
        return $this->isPrinting;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return GoodsDisc
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
     * @return GoodsDisc
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