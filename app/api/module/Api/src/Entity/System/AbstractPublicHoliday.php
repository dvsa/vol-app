<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

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
 * AbstractPublicHoliday Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="public_holiday",
 *    indexes={
 *        @ORM\Index(name="ix_public_holiday_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_public_holiday_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_public_holiday_public_holiday_date", columns={"public_holiday_date"})
 *    }
 * )
 */
abstract class AbstractPublicHoliday implements BundleSerializableInterface, JsonSerializable
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
     * Public holiday date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="public_holiday_date", nullable=false)
     */
    protected $publicHolidayDate;

    /**
     * isEngland
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_england", nullable=true)
     */
    protected $isEngland;

    /**
     * isWales
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_wales", nullable=true)
     */
    protected $isWales;

    /**
     * isScotland
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_scotland", nullable=true)
     */
    protected $isScotland;

    /**
     * isNi
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_ni", nullable=true)
     */
    protected $isNi;

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
     * @return PublicHoliday
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
     * @return PublicHoliday
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
     * @return PublicHoliday
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
     * Set the public holiday date
     *
     * @param \DateTime $publicHolidayDate new value being set
     *
     * @return PublicHoliday
     */
    public function setPublicHolidayDate($publicHolidayDate)
    {
        $this->publicHolidayDate = $publicHolidayDate;

        return $this;
    }

    /**
     * Get the public holiday date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPublicHolidayDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->publicHolidayDate);
        }

        return $this->publicHolidayDate;
    }

    /**
     * Set the is england
     *
     * @param string $isEngland new value being set
     *
     * @return PublicHoliday
     */
    public function setIsEngland($isEngland)
    {
        $this->isEngland = $isEngland;

        return $this;
    }

    /**
     * Get the is england
     *
     * @return string     */
    public function getIsEngland()
    {
        return $this->isEngland;
    }

    /**
     * Set the is wales
     *
     * @param string $isWales new value being set
     *
     * @return PublicHoliday
     */
    public function setIsWales($isWales)
    {
        $this->isWales = $isWales;

        return $this;
    }

    /**
     * Get the is wales
     *
     * @return string     */
    public function getIsWales()
    {
        return $this->isWales;
    }

    /**
     * Set the is scotland
     *
     * @param string $isScotland new value being set
     *
     * @return PublicHoliday
     */
    public function setIsScotland($isScotland)
    {
        $this->isScotland = $isScotland;

        return $this;
    }

    /**
     * Get the is scotland
     *
     * @return string     */
    public function getIsScotland()
    {
        return $this->isScotland;
    }

    /**
     * Set the is ni
     *
     * @param string $isNi new value being set
     *
     * @return PublicHoliday
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;

        return $this;
    }

    /**
     * Get the is ni
     *
     * @return string     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return PublicHoliday
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