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
 * AbstractIrfoVehicle Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irfo_vehicle",
 *    indexes={
 *        @ORM\Index(name="ix_irfo_vehicle_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irfo_vehicle_irfo_gv_permit_id", columns={"irfo_gv_permit_id"}),
 *        @ORM\Index(name="ix_irfo_vehicle_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="uk_irfo_vehicle_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_irfo_vehicle_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractIrfoVehicle implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to irfo_gv_permit
     *
     * @var \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit", fetch="LAZY")
     * @ORM\JoinColumn(name="irfo_gv_permit_id", referencedColumnName="id", nullable=true)
     */
    protected $irfoGvPermit;

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
     * cocA
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="coc_a", nullable=false, options={"default": 0})
     */
    protected $cocA = 0;

    /**
     * cocB
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="coc_b", nullable=false, options={"default": 0})
     */
    protected $cocB = 0;

    /**
     * cocC
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="coc_c", nullable=false, options={"default": 0})
     */
    protected $cocC = 0;

    /**
     * cocD
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="coc_d", nullable=false, options={"default": 0})
     */
    protected $cocD = 0;

    /**
     * cocT
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="coc_t", nullable=false, options={"default": 0})
     */
    protected $cocT = 0;

    /**
     * Vrm
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vrm", length=20, nullable=false)
     */
    protected $vrm = '';

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=true, options={"default": 1})
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
     * @return IrfoVehicle
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
     * Set the irfo gv permit
     *
     * @param \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit $irfoGvPermit new value being set
     *
     * @return IrfoVehicle
     */
    public function setIrfoGvPermit($irfoGvPermit)
    {
        $this->irfoGvPermit = $irfoGvPermit;

        return $this;
    }

    /**
     * Get the irfo gv permit
     *
     * @return \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit     */
    public function getIrfoGvPermit()
    {
        return $this->irfoGvPermit;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return IrfoVehicle
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
     * @return IrfoVehicle
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
     * Set the coc a
     *
     * @param string $cocA new value being set
     *
     * @return IrfoVehicle
     */
    public function setCocA($cocA)
    {
        $this->cocA = $cocA;

        return $this;
    }

    /**
     * Get the coc a
     *
     * @return string     */
    public function getCocA()
    {
        return $this->cocA;
    }

    /**
     * Set the coc b
     *
     * @param string $cocB new value being set
     *
     * @return IrfoVehicle
     */
    public function setCocB($cocB)
    {
        $this->cocB = $cocB;

        return $this;
    }

    /**
     * Get the coc b
     *
     * @return string     */
    public function getCocB()
    {
        return $this->cocB;
    }

    /**
     * Set the coc c
     *
     * @param string $cocC new value being set
     *
     * @return IrfoVehicle
     */
    public function setCocC($cocC)
    {
        $this->cocC = $cocC;

        return $this;
    }

    /**
     * Get the coc c
     *
     * @return string     */
    public function getCocC()
    {
        return $this->cocC;
    }

    /**
     * Set the coc d
     *
     * @param string $cocD new value being set
     *
     * @return IrfoVehicle
     */
    public function setCocD($cocD)
    {
        $this->cocD = $cocD;

        return $this;
    }

    /**
     * Get the coc d
     *
     * @return string     */
    public function getCocD()
    {
        return $this->cocD;
    }

    /**
     * Set the coc t
     *
     * @param string $cocT new value being set
     *
     * @return IrfoVehicle
     */
    public function setCocT($cocT)
    {
        $this->cocT = $cocT;

        return $this;
    }

    /**
     * Get the coc t
     *
     * @return string     */
    public function getCocT()
    {
        return $this->cocT;
    }

    /**
     * Set the vrm
     *
     * @param string $vrm new value being set
     *
     * @return IrfoVehicle
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
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrfoVehicle
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
     * @return IrfoVehicle
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