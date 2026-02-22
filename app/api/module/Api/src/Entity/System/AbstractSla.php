<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractSla Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="sla",
 *    indexes={
 *        @ORM\Index(name="uniqueCategoryFieldCompare", columns={"category", "field", "compare_to"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uniqueCategoryFieldCompare", columns={"category", "field", "compare_to"})
 *    }
 * )
 */
abstract class AbstractSla implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

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
     * e.g. PI
     *
     * @var string
     *
     * @ORM\Column(type="string", name="category", length=32, nullable=true)
     */
    protected $category;

    /**
     * Field holding source of sla
     *
     * @var string
     *
     * @ORM\Column(type="string", name="field", length=32, nullable=true)
     */
    protected $field;

    /**
     * Field holding result
     *
     * @var string
     *
     * @ORM\Column(type="string", name="compare_to", length=32, nullable=true)
     */
    protected $compareTo;

    /**
     * Number of days between source and result for succes. Can be negative
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="days", nullable=true)
     */
    protected $days;

    /**
     * Effective from
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="effective_from", nullable=true)
     */
    protected $effectiveFrom;

    /**
     * Effective to
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="effective_to", nullable=true)
     */
    protected $effectiveTo;

    /**
     * Include public holidays in SLA calculation
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="public_holiday", nullable=false, options={"default": 0})
     */
    protected $publicHoliday = 0;

    /**
     * Include weekends in SLA calculation
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="weekend", nullable=false, options={"default": 0})
     */
    protected $weekend = 0;


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Sla
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
     * Set the category
     *
     * @param string $category new value being set
     *
     * @return Sla
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the category
     *
     * @return string     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the field
     *
     * @param string $field new value being set
     *
     * @return Sla
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get the field
     *
     * @return string     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set the compare to
     *
     * @param string $compareTo new value being set
     *
     * @return Sla
     */
    public function setCompareTo($compareTo)
    {
        $this->compareTo = $compareTo;

        return $this;
    }

    /**
     * Get the compare to
     *
     * @return string     */
    public function getCompareTo()
    {
        return $this->compareTo;
    }

    /**
     * Set the days
     *
     * @param int $days new value being set
     *
     * @return Sla
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * Get the days
     *
     * @return int     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Set the effective from
     *
     * @param \DateTime $effectiveFrom new value being set
     *
     * @return Sla
     */
    public function setEffectiveFrom($effectiveFrom)
    {
        $this->effectiveFrom = $effectiveFrom;

        return $this;
    }

    /**
     * Get the effective from
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getEffectiveFrom($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveFrom);
        }

        return $this->effectiveFrom;
    }

    /**
     * Set the effective to
     *
     * @param \DateTime $effectiveTo new value being set
     *
     * @return Sla
     */
    public function setEffectiveTo($effectiveTo)
    {
        $this->effectiveTo = $effectiveTo;

        return $this;
    }

    /**
     * Get the effective to
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getEffectiveTo($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveTo);
        }

        return $this->effectiveTo;
    }

    /**
     * Set the public holiday
     *
     * @param bool $publicHoliday new value being set
     *
     * @return Sla
     */
    public function setPublicHoliday($publicHoliday)
    {
        $this->publicHoliday = $publicHoliday;

        return $this;
    }

    /**
     * Get the public holiday
     *
     * @return bool     */
    public function getPublicHoliday()
    {
        return $this->publicHoliday;
    }

    /**
     * Set the weekend
     *
     * @param bool $weekend new value being set
     *
     * @return Sla
     */
    public function setWeekend($weekend)
    {
        $this->weekend = $weekend;

        return $this;
    }

    /**
     * Get the weekend
     *
     * @return bool     */
    public function getWeekend()
    {
        return $this->weekend;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
