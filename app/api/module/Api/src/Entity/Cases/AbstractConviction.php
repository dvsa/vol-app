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
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractConviction Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="conviction",
 *    indexes={
 *        @ORM\Index(name="ix_conviction_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_conviction_conviction_category", columns={"conviction_category"}),
 *        @ORM\Index(name="ix_conviction_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_conviction_defendant_type", columns={"defendant_type"}),
 *        @ORM\Index(name="ix_conviction_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_conviction_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="uk_conviction_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_conviction_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractConviction implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

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
     * DefendantType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="defendant_type", referencedColumnName="id")
     */
    protected $defendantType;

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
     * ConvictionCategory
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="conviction_category", referencedColumnName="id", nullable=true)
     */
    protected $convictionCategory;

    /**
     * Foreign Key to transport_manager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id", nullable=true)
     */
    protected $transportManager;

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
     * Offence date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="offence_date", nullable=true)
     */
    protected $offenceDate;

    /**
     * Conviction date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="conviction_date", nullable=true)
     */
    protected $convictionDate;

    /**
     * Court
     *
     * @var string
     *
     * @ORM\Column(type="string", name="court", length=70, nullable=true)
     */
    protected $court;

    /**
     * Penalty
     *
     * @var string
     *
     * @ORM\Column(type="string", name="penalty", length=255, nullable=true)
     */
    protected $penalty;

    /**
     * New olcs field?
     *
     * @var string
     *
     * @ORM\Column(type="string", name="costs", length=255, nullable=true)
     */
    protected $costs;

    /**
     * msi
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="msi", nullable=true)
     */
    protected $msi;

    /**
     * isDealtWith
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_dealt_with", nullable=false, options={"default": 0})
     */
    protected $isDealtWith = 0;

    /**
     * Declared to TC
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_declared", nullable=false, options={"default": 0})
     */
    protected $isDeclared = 0;

    /**
     * Birth date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="birth_date", nullable=true)
     */
    protected $birthDate;

    /**
     * Person firstname
     *
     * @var string
     *
     * @ORM\Column(type="string", name="person_firstname", length=70, nullable=true)
     */
    protected $personFirstname;

    /**
     * Length 70 because of ETL. Will hold some org names from legacy data.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="person_lastname", length=70, nullable=true)
     */
    protected $personLastname;

    /**
     * Notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notes", length=4000, nullable=true)
     */
    protected $notes;

    /**
     * Taken into consideration
     *
     * @var string
     *
     * @ORM\Column(type="string", name="taken_into_consideration", length=4000, nullable=true)
     */
    protected $takenIntoConsideration;

    /**
     * user entered category for non act
     *
     * @var string
     *
     * @ORM\Column(type="string", name="category_text", length=1024, nullable=true)
     */
    protected $categoryText;

    /**
     * Set if defendant type is operator. Copy of op name at time of conviction.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="operator_name", length=70, nullable=true)
     */
    protected $operatorName;

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
     * Used to map FKs during ETL. Can be dropped safely when OLBS decommissioned
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

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
     * @return Conviction
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
     * Set the defendant type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $defendantType new value being set
     *
     * @return Conviction
     */
    public function setDefendantType($defendantType)
    {
        $this->defendantType = $defendantType;

        return $this;
    }

    /**
     * Get the defendant type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getDefendantType()
    {
        return $this->defendantType;
    }

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case new value being set
     *
     * @return Conviction
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
     * Set the conviction category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $convictionCategory new value being set
     *
     * @return Conviction
     */
    public function setConvictionCategory($convictionCategory)
    {
        $this->convictionCategory = $convictionCategory;

        return $this;
    }

    /**
     * Get the conviction category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getConvictionCategory()
    {
        return $this->convictionCategory;
    }

    /**
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager new value being set
     *
     * @return Conviction
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;

        return $this;
    }

    /**
     * Get the transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Conviction
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
     * @return Conviction
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
     * Set the offence date
     *
     * @param \DateTime $offenceDate new value being set
     *
     * @return Conviction
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
     * Set the conviction date
     *
     * @param \DateTime $convictionDate new value being set
     *
     * @return Conviction
     */
    public function setConvictionDate($convictionDate)
    {
        $this->convictionDate = $convictionDate;

        return $this;
    }

    /**
     * Get the conviction date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getConvictionDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->convictionDate);
        }

        return $this->convictionDate;
    }

    /**
     * Set the court
     *
     * @param string $court new value being set
     *
     * @return Conviction
     */
    public function setCourt($court)
    {
        $this->court = $court;

        return $this;
    }

    /**
     * Get the court
     *
     * @return string     */
    public function getCourt()
    {
        return $this->court;
    }

    /**
     * Set the penalty
     *
     * @param string $penalty new value being set
     *
     * @return Conviction
     */
    public function setPenalty($penalty)
    {
        $this->penalty = $penalty;

        return $this;
    }

    /**
     * Get the penalty
     *
     * @return string     */
    public function getPenalty()
    {
        return $this->penalty;
    }

    /**
     * Set the costs
     *
     * @param string $costs new value being set
     *
     * @return Conviction
     */
    public function setCosts($costs)
    {
        $this->costs = $costs;

        return $this;
    }

    /**
     * Get the costs
     *
     * @return string     */
    public function getCosts()
    {
        return $this->costs;
    }

    /**
     * Set the msi
     *
     * @param string $msi new value being set
     *
     * @return Conviction
     */
    public function setMsi($msi)
    {
        $this->msi = $msi;

        return $this;
    }

    /**
     * Get the msi
     *
     * @return string     */
    public function getMsi()
    {
        return $this->msi;
    }

    /**
     * Set the is dealt with
     *
     * @param string $isDealtWith new value being set
     *
     * @return Conviction
     */
    public function setIsDealtWith($isDealtWith)
    {
        $this->isDealtWith = $isDealtWith;

        return $this;
    }

    /**
     * Get the is dealt with
     *
     * @return string     */
    public function getIsDealtWith()
    {
        return $this->isDealtWith;
    }

    /**
     * Set the is declared
     *
     * @param string $isDeclared new value being set
     *
     * @return Conviction
     */
    public function setIsDeclared($isDeclared)
    {
        $this->isDeclared = $isDeclared;

        return $this;
    }

    /**
     * Get the is declared
     *
     * @return string     */
    public function getIsDeclared()
    {
        return $this->isDeclared;
    }

    /**
     * Set the birth date
     *
     * @param \DateTime $birthDate new value being set
     *
     * @return Conviction
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get the birth date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getBirthDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->birthDate);
        }

        return $this->birthDate;
    }

    /**
     * Set the person firstname
     *
     * @param string $personFirstname new value being set
     *
     * @return Conviction
     */
    public function setPersonFirstname($personFirstname)
    {
        $this->personFirstname = $personFirstname;

        return $this;
    }

    /**
     * Get the person firstname
     *
     * @return string     */
    public function getPersonFirstname()
    {
        return $this->personFirstname;
    }

    /**
     * Set the person lastname
     *
     * @param string $personLastname new value being set
     *
     * @return Conviction
     */
    public function setPersonLastname($personLastname)
    {
        $this->personLastname = $personLastname;

        return $this;
    }

    /**
     * Get the person lastname
     *
     * @return string     */
    public function getPersonLastname()
    {
        return $this->personLastname;
    }

    /**
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return Conviction
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
     * Set the taken into consideration
     *
     * @param string $takenIntoConsideration new value being set
     *
     * @return Conviction
     */
    public function setTakenIntoConsideration($takenIntoConsideration)
    {
        $this->takenIntoConsideration = $takenIntoConsideration;

        return $this;
    }

    /**
     * Get the taken into consideration
     *
     * @return string     */
    public function getTakenIntoConsideration()
    {
        return $this->takenIntoConsideration;
    }

    /**
     * Set the category text
     *
     * @param string $categoryText new value being set
     *
     * @return Conviction
     */
    public function setCategoryText($categoryText)
    {
        $this->categoryText = $categoryText;

        return $this;
    }

    /**
     * Get the category text
     *
     * @return string     */
    public function getCategoryText()
    {
        return $this->categoryText;
    }

    /**
     * Set the operator name
     *
     * @param string $operatorName new value being set
     *
     * @return Conviction
     */
    public function setOperatorName($operatorName)
    {
        $this->operatorName = $operatorName;

        return $this;
    }

    /**
     * Get the operator name
     *
     * @return string     */
    public function getOperatorName()
    {
        return $this->operatorName;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Conviction
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
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return Conviction
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}