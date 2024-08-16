<?php

namespace Dvsa\Olcs\Api\Entity\Irfo;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * IrfoPermitStock Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irfo_permit_stock",
 *    indexes={
 *        @ORM\Index(name="ix_irfo_permit_stock_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irfo_permit_stock_irfo_country_id", columns={"irfo_country_id"}),
 *        @ORM\Index(name="ix_irfo_permit_stock_irfo_gv_permit_id", columns={"irfo_gv_permit_id"}),
 *        @ORM\Index(name="ix_irfo_permit_stock_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_irfo_permit_stock_status", columns={"status"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_irfo_permit_stock_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractIrfoPermitStock implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Irfo country
     *
     * @var \Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry", fetch="LAZY")
     * @ORM\JoinColumn(name="irfo_country_id", referencedColumnName="id", nullable=false)
     */
    protected $irfoCountry;

    /**
     * Irfo gv permit
     *
     * @var \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit", fetch="LAZY")
     * @ORM\JoinColumn(name="irfo_gv_permit_id", referencedColumnName="id", nullable=true)
     */
    protected $irfoGvPermit;

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
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Serial no
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="serial_no", nullable=false)
     */
    protected $serialNo;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=false)
     */
    protected $status;

    /**
     * Valid for year
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="valid_for_year", nullable=false)
     */
    protected $validForYear;

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
     * Void return date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="void_return_date", nullable=true)
     */
    protected $voidReturnDate;

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return IrfoPermitStock
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
     * @return IrfoPermitStock
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
     * Set the irfo country
     *
     * @param \Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry $irfoCountry entity being set as the value
     *
     * @return IrfoPermitStock
     */
    public function setIrfoCountry($irfoCountry)
    {
        $this->irfoCountry = $irfoCountry;

        return $this;
    }

    /**
     * Get the irfo country
     *
     * @return \Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry
     */
    public function getIrfoCountry()
    {
        return $this->irfoCountry;
    }

    /**
     * Set the irfo gv permit
     *
     * @param \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit $irfoGvPermit entity being set as the value
     *
     * @return IrfoPermitStock
     */
    public function setIrfoGvPermit($irfoGvPermit)
    {
        $this->irfoGvPermit = $irfoGvPermit;

        return $this;
    }

    /**
     * Get the irfo gv permit
     *
     * @return \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit
     */
    public function getIrfoGvPermit()
    {
        return $this->irfoGvPermit;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return IrfoPermitStock
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
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return IrfoPermitStock
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int
     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the serial no
     *
     * @param int $serialNo new value being set
     *
     * @return IrfoPermitStock
     */
    public function setSerialNo($serialNo)
    {
        $this->serialNo = $serialNo;

        return $this;
    }

    /**
     * Get the serial no
     *
     * @return int
     */
    public function getSerialNo()
    {
        return $this->serialNo;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status entity being set as the value
     *
     * @return IrfoPermitStock
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the valid for year
     *
     * @param int $validForYear new value being set
     *
     * @return IrfoPermitStock
     */
    public function setValidForYear($validForYear)
    {
        $this->validForYear = $validForYear;

        return $this;
    }

    /**
     * Get the valid for year
     *
     * @return int
     */
    public function getValidForYear()
    {
        return $this->validForYear;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrfoPermitStock
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
     * Set the void return date
     *
     * @param \DateTime $voidReturnDate new value being set
     *
     * @return IrfoPermitStock
     */
    public function setVoidReturnDate($voidReturnDate)
    {
        $this->voidReturnDate = $voidReturnDate;

        return $this;
    }

    /**
     * Get the void return date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getVoidReturnDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->voidReturnDate);
        }

        return $this->voidReturnDate;
    }
}
