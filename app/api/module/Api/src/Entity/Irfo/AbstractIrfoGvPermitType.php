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
 * AbstractIrfoGvPermitType Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irfo_gv_permit_type",
 *    indexes={
 *        @ORM\Index(name="ix_irfo_gv_permit_type_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irfo_gv_permit_type_irfo_country_id", columns={"irfo_country_id"}),
 *        @ORM\Index(name="ix_irfo_gv_permit_type_irfo_fee_type", columns={"irfo_fee_type"}),
 *        @ORM\Index(name="ix_irfo_gv_permit_type_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractIrfoGvPermitType implements BundleSerializableInterface, JsonSerializable
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
     * IrfoFeeType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="irfo_fee_type", referencedColumnName="id")
     */
    protected $irfoFeeType;

    /**
     * Foreign Key to irfo_country
     *
     * @var \Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry", fetch="LAZY")
     * @ORM\JoinColumn(name="irfo_country_id", referencedColumnName="id", nullable=true)
     */
    protected $irfoCountry;

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
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=100, nullable=false)
     */
    protected $description = '';

    /**
     * Display until
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="display_until", nullable=true)
     */
    protected $displayUntil;

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
     * @return IrfoGvPermitType
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
     * Set the irfo fee type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $irfoFeeType new value being set
     *
     * @return IrfoGvPermitType
     */
    public function setIrfoFeeType($irfoFeeType)
    {
        $this->irfoFeeType = $irfoFeeType;

        return $this;
    }

    /**
     * Get the irfo fee type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getIrfoFeeType()
    {
        return $this->irfoFeeType;
    }

    /**
     * Set the irfo country
     *
     * @param \Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry $irfoCountry new value being set
     *
     * @return IrfoGvPermitType
     */
    public function setIrfoCountry($irfoCountry)
    {
        $this->irfoCountry = $irfoCountry;

        return $this;
    }

    /**
     * Get the irfo country
     *
     * @return \Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry     */
    public function getIrfoCountry()
    {
        return $this->irfoCountry;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return IrfoGvPermitType
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
     * @return IrfoGvPermitType
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
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return IrfoGvPermitType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the display until
     *
     * @param \DateTime $displayUntil new value being set
     *
     * @return IrfoGvPermitType
     */
    public function setDisplayUntil($displayUntil)
    {
        $this->displayUntil = $displayUntil;

        return $this;
    }

    /**
     * Get the display until
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDisplayUntil($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->displayUntil);
        }

        return $this->displayUntil;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrfoGvPermitType
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