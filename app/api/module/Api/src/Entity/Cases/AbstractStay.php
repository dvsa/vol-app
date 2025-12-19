<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Cases;

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
 * AbstractStay Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="stay",
 *    indexes={
 *        @ORM\Index(name="ix_stay_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_stay_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_stay_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_stay_outcome", columns={"outcome"}),
 *        @ORM\Index(name="ix_stay_stay_type", columns={"stay_type"})
 *    }
 * )
 */
abstract class AbstractStay implements BundleSerializableInterface, JsonSerializable
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
     * Outcome
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="outcome", referencedColumnName="id", nullable=true)
     */
    protected $outcome;

    /**
     * TC or UT
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="stay_type", referencedColumnName="id")
     */
    protected $stayType;

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
     * Request date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="request_date", nullable=true)
     */
    protected $requestDate;

    /**
     * Withdrawn date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="withdrawn_date", nullable=true)
     */
    protected $withdrawnDate;

    /**
     * Decision date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="decision_date", nullable=true)
     */
    protected $decisionDate;

    /**
     * Notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notes", length=1100, nullable=true)
     */
    protected $notes;

    /**
     * dvsaNotified
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="dvsa_notified", nullable=false, options={"default": 0})
     */
    protected $dvsaNotified = 0;

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
     * @return Stay
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
     * @return Stay
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
     * Set the outcome
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $outcome new value being set
     *
     * @return Stay
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;

        return $this;
    }

    /**
     * Get the outcome
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * Set the stay type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $stayType new value being set
     *
     * @return Stay
     */
    public function setStayType($stayType)
    {
        $this->stayType = $stayType;

        return $this;
    }

    /**
     * Get the stay type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getStayType()
    {
        return $this->stayType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Stay
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
     * @return Stay
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
     * Set the request date
     *
     * @param \DateTime $requestDate new value being set
     *
     * @return Stay
     */
    public function setRequestDate($requestDate)
    {
        $this->requestDate = $requestDate;

        return $this;
    }

    /**
     * Get the request date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRequestDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->requestDate);
        }

        return $this->requestDate;
    }

    /**
     * Set the withdrawn date
     *
     * @param \DateTime $withdrawnDate new value being set
     *
     * @return Stay
     */
    public function setWithdrawnDate($withdrawnDate)
    {
        $this->withdrawnDate = $withdrawnDate;

        return $this;
    }

    /**
     * Get the withdrawn date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getWithdrawnDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->withdrawnDate);
        }

        return $this->withdrawnDate;
    }

    /**
     * Set the decision date
     *
     * @param \DateTime $decisionDate new value being set
     *
     * @return Stay
     */
    public function setDecisionDate($decisionDate)
    {
        $this->decisionDate = $decisionDate;

        return $this;
    }

    /**
     * Get the decision date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDecisionDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->decisionDate);
        }

        return $this->decisionDate;
    }

    /**
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return Stay
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
     * Set the dvsa notified
     *
     * @param string $dvsaNotified new value being set
     *
     * @return Stay
     */
    public function setDvsaNotified($dvsaNotified)
    {
        $this->dvsaNotified = $dvsaNotified;

        return $this;
    }

    /**
     * Get the dvsa notified
     *
     * @return string     */
    public function getDvsaNotified()
    {
        return $this->dvsaNotified;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Stay
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