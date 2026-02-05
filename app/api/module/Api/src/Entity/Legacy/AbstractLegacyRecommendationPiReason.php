<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Legacy;

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
 * AbstractLegacyRecommendationPiReason Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="legacy_recommendation_pi_reason",
 *    indexes={
 *        @ORM\Index(name="ix_legacy_recommendation_pi_reason_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_legacy_recommendation_pi_reason_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_legacy_recommendation_pi_reason_legacy_pi_reason_id", columns={"legacy_pi_reason_id"}),
 *        @ORM\Index(name="ix_legacy_recommendation_pi_reason_legacy_recommendation_id", columns={"legacy_recommendation_id"})
 *    }
 * )
 */
abstract class AbstractLegacyRecommendationPiReason implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    protected $id = 0;

    /**
     * Foreign Key to legacy_recommendation
     *
     * @var \Dvsa\Olcs\Api\Entity\Legacy\LegacyRecommendation
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Legacy\LegacyRecommendation", fetch="LAZY")
     * @ORM\JoinColumn(name="legacy_recommendation_id", referencedColumnName="id")
     */
    protected $legacyRecommendation;

    /**
     * Foreign Key to legacy_pi_reason
     *
     * @var \Dvsa\Olcs\Api\Entity\Legacy\LegacyPiReason
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Legacy\LegacyPiReason", fetch="LAZY")
     * @ORM\JoinColumn(name="legacy_pi_reason_id", referencedColumnName="id")
     */
    protected $legacyPiReason;

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
     * Comment
     *
     * @var string
     *
     * @ORM\Column(type="string", name="comment", length=30, nullable=true)
     */
    protected $comment;

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
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the legacy recommendation
     *
     * @param \Dvsa\Olcs\Api\Entity\Legacy\LegacyRecommendation $legacyRecommendation new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Legacy\LegacyRecommendation     */
    public function getLegacyRecommendation()
    {
        return $this->legacyRecommendation;
    }

    /**
     * Set the legacy pi reason
     *
     * @param \Dvsa\Olcs\Api\Entity\Legacy\LegacyPiReason $legacyPiReason new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Legacy\LegacyPiReason     */
    public function getLegacyPiReason()
    {
        return $this->legacyPiReason;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

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
     * @return string     */
    public function getComment()
    {
        return $this->comment;
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
