<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SiPenaltyErruRequested Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="si_penalty_erru_requested",
 *    indexes={
 *        @ORM\Index(name="ix_si_penalty_erru_requested_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_penalty_erru_requested_last_modified_by",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_si_penalty_erru_requested_serious_infringement_id",
     *     columns={"serious_infringement_id"}),
 *        @ORM\Index(name="ix_si_penalty_erru_requested_si_penalty_requested_type_id",
     *     columns={"si_penalty_requested_type_id"})
 *    }
 * )
 */
abstract class AbstractSiPenaltyErruRequested implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

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
     * Duration
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="duration", nullable=true)
     */
    protected $duration;

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
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Serious infringement
     *
     * @var \Dvsa\Olcs\Api\Entity\Si\SeriousInfringement
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Si\SeriousInfringement",
     *     fetch="LAZY",
     *     inversedBy="requestedErrus"
     * )
     * @ORM\JoinColumn(name="serious_infringement_id", referencedColumnName="id", nullable=false)
     */
    protected $seriousInfringement;

    /**
     * Si penalty requested type
     *
     * @var \Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType", fetch="LAZY")
     * @ORM\JoinColumn(name="si_penalty_requested_type_id", referencedColumnName="id", nullable=false)
     */
    protected $siPenaltyRequestedType;

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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return SiPenaltyErruRequested
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
     * Set the duration
     *
     * @param int $duration new value being set
     *
     * @return SiPenaltyErruRequested
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get the duration
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return SiPenaltyErruRequested
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
     * @return SiPenaltyErruRequested
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
     * @return SiPenaltyErruRequested
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
     * Set the serious infringement
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\SeriousInfringement $seriousInfringement entity being set as the value
     *
     * @return SiPenaltyErruRequested
     */
    public function setSeriousInfringement($seriousInfringement)
    {
        $this->seriousInfringement = $seriousInfringement;

        return $this;
    }

    /**
     * Get the serious infringement
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\SeriousInfringement
     */
    public function getSeriousInfringement()
    {
        return $this->seriousInfringement;
    }

    /**
     * Set the si penalty requested type
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType $siPenaltyRequestedType entity being set as the value
     *
     * @return SiPenaltyErruRequested
     */
    public function setSiPenaltyRequestedType($siPenaltyRequestedType)
    {
        $this->siPenaltyRequestedType = $siPenaltyRequestedType;

        return $this;
    }

    /**
     * Get the si penalty requested type
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType
     */
    public function getSiPenaltyRequestedType()
    {
        return $this->siPenaltyRequestedType;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SiPenaltyErruRequested
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
