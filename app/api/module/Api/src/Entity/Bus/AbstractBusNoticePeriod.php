<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

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
 * BusNoticePeriod Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="bus_notice_period",
 *    indexes={
 *        @ORM\Index(name="ix_bus_notice_period_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_bus_notice_period_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractBusNoticePeriod implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Cancellation period
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="cancellation_period", nullable=false)
     */
    protected $cancellationPeriod;

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
     * Notice area
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notice_area", length=70, nullable=false)
     */
    protected $noticeArea;

    /**
     * Standard period
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="standard_period", nullable=false)
     */
    protected $standardPeriod;

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
     * Set the cancellation period
     *
     * @param int $cancellationPeriod new value being set
     *
     * @return BusNoticePeriod
     */
    public function setCancellationPeriod($cancellationPeriod)
    {
        $this->cancellationPeriod = $cancellationPeriod;

        return $this;
    }

    /**
     * Get the cancellation period
     *
     * @return int
     */
    public function getCancellationPeriod()
    {
        return $this->cancellationPeriod;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return BusNoticePeriod
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
     * @return BusNoticePeriod
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
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return BusNoticePeriod
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
     * Set the notice area
     *
     * @param string $noticeArea new value being set
     *
     * @return BusNoticePeriod
     */
    public function setNoticeArea($noticeArea)
    {
        $this->noticeArea = $noticeArea;

        return $this;
    }

    /**
     * Get the notice area
     *
     * @return string
     */
    public function getNoticeArea()
    {
        return $this->noticeArea;
    }

    /**
     * Set the standard period
     *
     * @param int $standardPeriod new value being set
     *
     * @return BusNoticePeriod
     */
    public function setStandardPeriod($standardPeriod)
    {
        $this->standardPeriod = $standardPeriod;

        return $this;
    }

    /**
     * Get the standard period
     *
     * @return int
     */
    public function getStandardPeriod()
    {
        return $this->standardPeriod;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return BusNoticePeriod
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
}
