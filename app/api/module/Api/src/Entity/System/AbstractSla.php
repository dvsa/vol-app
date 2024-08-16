<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Sla Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="sla",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uniqueCategoryFieldCompare", columns={"category","field","compare_to"})
 *    }
 * )
 */
abstract class AbstractSla implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

    /**
     * Category
     *
     * @var string
     *
     * @ORM\Column(type="string", name="category", length=32, nullable=true)
     */
    protected $category;

    /**
     * Compare to
     *
     * @var string
     *
     * @ORM\Column(type="string", name="compare_to", length=32, nullable=true)
     */
    protected $compareTo;

    /**
     * Days
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
     * Field
     *
     * @var string
     *
     * @ORM\Column(type="string", name="field", length=32, nullable=true)
     */
    protected $field;

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
     * Public holiday
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="public_holiday", nullable=false, options={"default": 0})
     */
    protected $publicHoliday = 0;

    /**
     * Weekend
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="weekend", nullable=false, options={"default": 0})
     */
    protected $weekend = 0;

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
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
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
     * @return string
     */
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
     * @return int
     */
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
     * @return \DateTime|string

     */
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
     * @return \DateTime|string

     */
    public function getEffectiveTo($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveTo);
        }

        return $this->effectiveTo;
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
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the public holiday
     *
     * @param boolean $publicHoliday new value being set
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
     * @return boolean
     */
    public function getPublicHoliday()
    {
        return $this->publicHoliday;
    }

    /**
     * Set the weekend
     *
     * @param boolean $weekend new value being set
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
     * @return boolean
     */
    public function getWeekend()
    {
        return $this->weekend;
    }
}
