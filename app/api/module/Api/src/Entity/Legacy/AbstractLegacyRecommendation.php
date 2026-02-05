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
 * AbstractLegacyRecommendation Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="legacy_recommendation",
 *    indexes={
 *        @ORM\Index(name="ix_legacy_recommendation_action_id", columns={"action_id"}),
 *        @ORM\Index(name="ix_legacy_recommendation_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_legacy_recommendation_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_legacy_recommendation_from_user_id", columns={"from_user_id"}),
 *        @ORM\Index(name="ix_legacy_recommendation_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_legacy_recommendation_to_user_id", columns={"to_user_id"})
 *    }
 * )
 */
abstract class AbstractLegacyRecommendation implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    protected $case;

    /**
     * Action
     *
     * @var \Dvsa\Olcs\Api\Entity\Legacy\LegacyCaseAction
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Legacy\LegacyCaseAction", fetch="LAZY")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     */
    protected $action;

    /**
     * FromUser
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="from_user_id", referencedColumnName="id", nullable=true)
     */
    protected $fromUser;

    /**
     * ToUser
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="to_user_id", referencedColumnName="id", nullable=true)
     */
    protected $toUser;

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
     * Rec date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="rec_date", nullable=false)
     */
    protected $recDate;

    /**
     * Pi reason
     *
     * @var string
     *
     * @ORM\Column(type="string", name="pi_reason", length=255, nullable=true)
     */
    protected $piReason;

    /**
     * Comment
     *
     * @var string
     *
     * @ORM\Column(type="string", name="comment", length=4000, nullable=true)
     */
    protected $comment;

    /**
     * Effective date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="effective_date", nullable=true)
     */
    protected $effectiveDate;

    /**
     * Notes
     *
     * @var string
     *
     * @ORM\Column(type="text", name="notes", nullable=true)
     */
    protected $notes;

    /**
     * Pi decision
     *
     * @var string
     *
     * @ORM\Column(type="string", name="pi_decision", length=255, nullable=true)
     */
    protected $piDecision;

    /**
     * Request
     *
     * @var string
     *
     * @ORM\Column(type="string", name="request", length=20, nullable=true)
     */
    protected $request;

    /**
     * revokeLic
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="revoke_lic", nullable=true)
     */
    protected $revokeLic;

    /**
     * status
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="status", nullable=true)
     */
    protected $status;

    /**
     * Total points
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="total_points", nullable=true)
     */
    protected $totalPoints;

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
     * @return LegacyRecommendation
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
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case new value being set
     *
     * @return LegacyRecommendation
     */
    public function setCase($case)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * Get the case
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the action
     *
     * @param \Dvsa\Olcs\Api\Entity\Legacy\LegacyCaseAction $action new value being set
     *
     * @return LegacyRecommendation
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the action
     *
     * @return \Dvsa\Olcs\Api\Entity\Legacy\LegacyCaseAction     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the from user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $fromUser new value being set
     *
     * @return LegacyRecommendation
     */
    public function setFromUser($fromUser)
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    /**
     * Get the from user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set the to user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $toUser new value being set
     *
     * @return LegacyRecommendation
     */
    public function setToUser($toUser)
    {
        $this->toUser = $toUser;

        return $this;
    }

    /**
     * Get the to user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getToUser()
    {
        return $this->toUser;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LegacyRecommendation
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
     * @return LegacyRecommendation
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
     * Set the rec date
     *
     * @param \DateTime $recDate new value being set
     *
     * @return LegacyRecommendation
     */
    public function setRecDate($recDate)
    {
        $this->recDate = $recDate;

        return $this;
    }

    /**
     * Get the rec date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRecDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->recDate);
        }

        return $this->recDate;
    }

    /**
     * Set the pi reason
     *
     * @param string $piReason new value being set
     *
     * @return LegacyRecommendation
     */
    public function setPiReason($piReason)
    {
        $this->piReason = $piReason;

        return $this;
    }

    /**
     * Get the pi reason
     *
     * @return string     */
    public function getPiReason()
    {
        return $this->piReason;
    }

    /**
     * Set the comment
     *
     * @param string $comment new value being set
     *
     * @return LegacyRecommendation
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
     * Set the effective date
     *
     * @param \DateTime $effectiveDate new value being set
     *
     * @return LegacyRecommendation
     */
    public function setEffectiveDate($effectiveDate)
    {
        $this->effectiveDate = $effectiveDate;

        return $this;
    }

    /**
     * Get the effective date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getEffectiveDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveDate);
        }

        return $this->effectiveDate;
    }

    /**
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return LegacyRecommendation
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get the notes
     *
     * @return string     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set the pi decision
     *
     * @param string $piDecision new value being set
     *
     * @return LegacyRecommendation
     */
    public function setPiDecision($piDecision)
    {
        $this->piDecision = $piDecision;

        return $this;
    }

    /**
     * Get the pi decision
     *
     * @return string     */
    public function getPiDecision()
    {
        return $this->piDecision;
    }

    /**
     * Set the request
     *
     * @param string $request new value being set
     *
     * @return LegacyRecommendation
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the request
     *
     * @return string     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the revoke lic
     *
     * @param string $revokeLic new value being set
     *
     * @return LegacyRecommendation
     */
    public function setRevokeLic($revokeLic)
    {
        $this->revokeLic = $revokeLic;

        return $this;
    }

    /**
     * Get the revoke lic
     *
     * @return string     */
    public function getRevokeLic()
    {
        return $this->revokeLic;
    }

    /**
     * Set the status
     *
     * @param string $status new value being set
     *
     * @return LegacyRecommendation
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return string     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the total points
     *
     * @param int $totalPoints new value being set
     *
     * @return LegacyRecommendation
     */
    public function setTotalPoints($totalPoints)
    {
        $this->totalPoints = $totalPoints;

        return $this;
    }

    /**
     * Get the total points
     *
     * @return int     */
    public function getTotalPoints()
    {
        return $this->totalPoints;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LegacyRecommendation
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
