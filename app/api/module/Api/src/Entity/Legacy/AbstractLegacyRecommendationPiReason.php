<?php

namespace Dvsa\Olcs\Api\Entity\Legacy;

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
 * LegacyRecommendationPiReason Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="legacy_recommendation_pi_reason",
 *    indexes={
 *        @ORM\Index(name="ix_legacy_recommendation_pi_reason_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_legacy_recommendation_pi_reason_last_modified_by",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_legacy_recommendation_pi_reason_legacy_pi_reason_id",
     *     columns={"legacy_pi_reason_id"}),
 *        @ORM\Index(name="ix_legacy_recommendation_pi_reason_legacy_recommendation_id",
     *     columns={"legacy_recommendation_id"})
 *    }
 * )
 */
abstract class AbstractLegacyRecommendationPiReason implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Comment
     *
     * @var string
     *
     * @ORM\Column(type="string", name="comment", length=30, nullable=true)
     */
    protected $comment;

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
     * Legacy pi reason
     *
     * @var \Dvsa\Olcs\Api\Entity\Legacy\LegacyPiReason
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Legacy\LegacyPiReason", fetch="LAZY")
     * @ORM\JoinColumn(name="legacy_pi_reason_id", referencedColumnName="id", nullable=false)
     */
    protected $legacyPiReason;

    /**
     * Legacy recommendation
     *
     * @var \Dvsa\Olcs\Api\Entity\Legacy\LegacyRecommendation
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Legacy\LegacyRecommendation", fetch="LAZY")
     * @ORM\JoinColumn(name="legacy_recommendation_id", referencedColumnName="id", nullable=false)
     */
    protected $legacyRecommendation;

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
     * Set the comment
     *
     * @param string $comment new value being set
     *
     * @return LegacyRecommendationPiReason
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return LegacyRecommendationPiReason
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
     * @return LegacyRecommendationPiReason
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
     * @return LegacyRecommendationPiReason
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
     * Set the legacy pi reason
     *
     * @param \Dvsa\Olcs\Api\Entity\Legacy\LegacyPiReason $legacyPiReason entity being set as the value
     *
     * @return LegacyRecommendationPiReason
     */
    public function setLegacyPiReason($legacyPiReason)
    {
        $this->legacyPiReason = $legacyPiReason;

        return $this;
    }

    /**
     * Get the legacy pi reason
     *
     * @return \Dvsa\Olcs\Api\Entity\Legacy\LegacyPiReason
     */
    public function getLegacyPiReason()
    {
        return $this->legacyPiReason;
    }

    /**
     * Set the legacy recommendation
     *
     * @param \Dvsa\Olcs\Api\Entity\Legacy\LegacyRecommendation $legacyRecommendation entity being set as the value
     *
     * @return LegacyRecommendationPiReason
     */
    public function setLegacyRecommendation($legacyRecommendation)
    {
        $this->legacyRecommendation = $legacyRecommendation;

        return $this;
    }

    /**
     * Get the legacy recommendation
     *
     * @return \Dvsa\Olcs\Api\Entity\Legacy\LegacyRecommendation
     */
    public function getLegacyRecommendation()
    {
        return $this->legacyRecommendation;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LegacyRecommendationPiReason
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
