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
 * AbstractLegacyOffence Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="legacy_offence",
 *    indexes={
 *        @ORM\Index(name="ix_legacy_offence_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_legacy_offence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_legacy_offence_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractLegacyOffence implements BundleSerializableInterface, JsonSerializable
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
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    protected $case;

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
     * Definition
     *
     * @var string
     *
     * @ORM\Column(type="string", name="definition", length=1000, nullable=true)
     */
    protected $definition;

    /**
     * isTrailer
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_trailer", nullable=true)
     */
    protected $isTrailer;

    /**
     * Notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notes", length=4000, nullable=true)
     */
    protected $notes;

    /**
     * Num of offences
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="num_of_offences", nullable=true)
     */
    protected $numOfOffences;

    /**
     * Offence authority
     *
     * @var string
     *
     * @ORM\Column(type="string", name="offence_authority", length=100, nullable=true)
     */
    protected $offenceAuthority;

    /**
     * Offence date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="offence_date", nullable=true)
     */
    protected $offenceDate;

    /**
     * Offence to date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="offence_to_date", nullable=true)
     */
    protected $offenceToDate;

    /**
     * Offender name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="offender_name", length=100, nullable=true)
     */
    protected $offenderName;

    /**
     * Points
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="points", nullable=true)
     */
    protected $points;

    /**
     * Position
     *
     * @var string
     *
     * @ORM\Column(type="string", name="position", length=100, nullable=true)
     */
    protected $position;

    /**
     * Offence type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="offence_type", length=100, nullable=true)
     */
    protected $offenceType;

    /**
     * Vrm
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vrm", length=20, nullable=true)
     */
    protected $vrm;

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
     * @return LegacyOffence
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
     * @return LegacyOffence
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LegacyOffence
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
     * @return LegacyOffence
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
     * Set the definition
     *
     * @param string $definition new value being set
     *
     * @return LegacyOffence
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Get the definition
     *
     * @return string     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set the is trailer
     *
     * @param string $isTrailer new value being set
     *
     * @return LegacyOffence
     */
    public function setIsTrailer($isTrailer)
    {
        $this->isTrailer = $isTrailer;

        return $this;
    }

    /**
     * Get the is trailer
     *
     * @return string     */
    public function getIsTrailer()
    {
        return $this->isTrailer;
    }

    /**
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return LegacyOffence
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
     * Set the num of offences
     *
     * @param int $numOfOffences new value being set
     *
     * @return LegacyOffence
     */
    public function setNumOfOffences($numOfOffences)
    {
        $this->numOfOffences = $numOfOffences;

        return $this;
    }

    /**
     * Get the num of offences
     *
     * @return int     */
    public function getNumOfOffences()
    {
        return $this->numOfOffences;
    }

    /**
     * Set the offence authority
     *
     * @param string $offenceAuthority new value being set
     *
     * @return LegacyOffence
     */
    public function setOffenceAuthority($offenceAuthority)
    {
        $this->offenceAuthority = $offenceAuthority;

        return $this;
    }

    /**
     * Get the offence authority
     *
     * @return string     */
    public function getOffenceAuthority()
    {
        return $this->offenceAuthority;
    }

    /**
     * Set the offence date
     *
     * @param \DateTime $offenceDate new value being set
     *
     * @return LegacyOffence
     */
    public function setOffenceDate($offenceDate)
    {
        $this->offenceDate = $offenceDate;

        return $this;
    }

    /**
     * Get the offence date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getOffenceDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->offenceDate);
        }

        return $this->offenceDate;
    }

    /**
     * Set the offence to date
     *
     * @param \DateTime $offenceToDate new value being set
     *
     * @return LegacyOffence
     */
    public function setOffenceToDate($offenceToDate)
    {
        $this->offenceToDate = $offenceToDate;

        return $this;
    }

    /**
     * Get the offence to date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getOffenceToDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->offenceToDate);
        }

        return $this->offenceToDate;
    }

    /**
     * Set the offender name
     *
     * @param string $offenderName new value being set
     *
     * @return LegacyOffence
     */
    public function setOffenderName($offenderName)
    {
        $this->offenderName = $offenderName;

        return $this;
    }

    /**
     * Get the offender name
     *
     * @return string     */
    public function getOffenderName()
    {
        return $this->offenderName;
    }

    /**
     * Set the points
     *
     * @param int $points new value being set
     *
     * @return LegacyOffence
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get the points
     *
     * @return int     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set the position
     *
     * @param string $position new value being set
     *
     * @return LegacyOffence
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get the position
     *
     * @return string     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set the offence type
     *
     * @param string $offenceType new value being set
     *
     * @return LegacyOffence
     */
    public function setOffenceType($offenceType)
    {
        $this->offenceType = $offenceType;

        return $this;
    }

    /**
     * Get the offence type
     *
     * @return string     */
    public function getOffenceType()
    {
        return $this->offenceType;
    }

    /**
     * Set the vrm
     *
     * @param string $vrm new value being set
     *
     * @return LegacyOffence
     */
    public function setVrm($vrm)
    {
        $this->vrm = $vrm;

        return $this;
    }

    /**
     * Get the vrm
     *
     * @return string     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LegacyOffence
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