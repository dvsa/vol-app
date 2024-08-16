<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Continuation Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="continuation",
 *    indexes={
 *        @ORM\Index(name="ix_continuation_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_continuation_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_continuation_traffic_area_id_year_month",
     *     columns={"traffic_area_id","year","month"})
 *    }
 * )
 */
abstract class AbstractContinuation implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;

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
     * Month
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="month", nullable=false)
     */
    protected $month;

    /**
     * Traffic area
     *
     * @var \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea", fetch="LAZY")
     * @ORM\JoinColumn(name="traffic_area_id", referencedColumnName="id", nullable=false)
     */
    protected $trafficArea;

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
     * Year
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="year", nullable=false)
     */
    protected $year;

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Continuation
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
     * @return Continuation
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
     * Set the month
     *
     * @param int $month new value being set
     *
     * @return Continuation
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get the month
     *
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set the traffic area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea $trafficArea entity being set as the value
     *
     * @return Continuation
     */
    public function setTrafficArea($trafficArea)
    {
        $this->trafficArea = $trafficArea;

        return $this;
    }

    /**
     * Get the traffic area
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Continuation
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
     * Set the year
     *
     * @param int $year new value being set
     *
     * @return Continuation
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get the year
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }
}
