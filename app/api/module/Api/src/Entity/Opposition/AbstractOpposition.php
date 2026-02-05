<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Opposition;

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
 * AbstractOpposition Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="opposition",
 *    indexes={
 *        @ORM\Index(name="ix_opposition_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_opposition_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_opposition_is_valid", columns={"is_valid"}),
 *        @ORM\Index(name="ix_opposition_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_opposition_opposer_id", columns={"opposer_id"}),
 *        @ORM\Index(name="ix_opposition_opposition_type", columns={"opposition_type"}),
 *        @ORM\Index(name="ix_opposition_status", columns={"status"}),
 *        @ORM\Index(name="uk_opposition_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_opposition_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"})
 *    }
 * )
 */
abstract class AbstractOpposition implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    protected $case;

    /**
     * Foreign Key to opposer
     *
     * @var \Dvsa\Olcs\Api\Entity\Opposition\Opposer
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Opposition\Opposer", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="opposer_id", referencedColumnName="id")
     */
    protected $opposer;

    /**
     * OppositionType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="opposition_type", referencedColumnName="id")
     */
    protected $oppositionType;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=true)
     */
    protected $status;

    /**
     * yes, no, undecided
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="is_valid", referencedColumnName="id")
     */
    protected $isValid;

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
     * Notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notes", length=4000, nullable=true)
     */
    protected $notes;

    /**
     * isCopied
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_copied", nullable=false, options={"default": 0})
     */
    protected $isCopied = 0;

    /**
     * Raised date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="raised_date", nullable=true)
     */
    protected $raisedDate;

    /**
     * isInTime
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_in_time", nullable=false, options={"default": 0})
     */
    protected $isInTime = 0;

    /**
     * isWithdrawn
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_withdrawn", nullable=false, options={"default": 0})
     */
    protected $isWithdrawn = 0;

    /**
     * Valid notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="valid_notes", length=4000, nullable=true)
     */
    protected $validNotes;

    /**
     * isWillingToAttendPi
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_willing_to_attend_pi", nullable=false, options={"default": 0})
     */
    protected $isWillingToAttendPi = 0;

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
     * used to differntiate source of data during ETL when one OLCS table relates to many OLBS. Can be dropped when fully live
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;

    /**
     * OperatingCentres
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre", mappedBy="oppositions", fetch="LAZY")
     */
    protected $operatingCentres;

    /**
     * Grounds
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", inversedBy="oppositions", fetch="LAZY")
     * @ORM\JoinTable(name="opposition_grounds",
     *     joinColumns={
     *         @ORM\JoinColumn(name="opposition_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="ground_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $grounds;

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
        $this->operatingCentres = new ArrayCollection();
        $this->grounds = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Opposition
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
     * @return Opposition
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
     * Set the opposer
     *
     * @param \Dvsa\Olcs\Api\Entity\Opposition\Opposer $opposer new value being set
     *
     * @return Opposition
     */
    public function setOpposer($opposer)
    {
        $this->opposer = $opposer;

        return $this;
    }

    /**
     * Get the opposer
     *
     * @return \Dvsa\Olcs\Api\Entity\Opposition\Opposer     */
    public function getOpposer()
    {
        return $this->opposer;
    }

    /**
     * Set the opposition type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $oppositionType new value being set
     *
     * @return Opposition
     */
    public function setOppositionType($oppositionType)
    {
        $this->oppositionType = $oppositionType;

        return $this;
    }

    /**
     * Get the opposition type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getOppositionType()
    {
        return $this->oppositionType;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status new value being set
     *
     * @return Opposition
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the is valid
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $isValid new value being set
     *
     * @return Opposition
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Get the is valid
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Opposition
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
     * @return Opposition
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
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return Opposition
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
     * Set the is copied
     *
     * @param string $isCopied new value being set
     *
     * @return Opposition
     */
    public function setIsCopied($isCopied)
    {
        $this->isCopied = $isCopied;

        return $this;
    }

    /**
     * Get the is copied
     *
     * @return string     */
    public function getIsCopied()
    {
        return $this->isCopied;
    }

    /**
     * Set the raised date
     *
     * @param \DateTime $raisedDate new value being set
     *
     * @return Opposition
     */
    public function setRaisedDate($raisedDate)
    {
        $this->raisedDate = $raisedDate;

        return $this;
    }

    /**
     * Get the raised date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRaisedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->raisedDate);
        }

        return $this->raisedDate;
    }

    /**
     * Set the is in time
     *
     * @param string $isInTime new value being set
     *
     * @return Opposition
     */
    public function setIsInTime($isInTime)
    {
        $this->isInTime = $isInTime;

        return $this;
    }

    /**
     * Get the is in time
     *
     * @return string     */
    public function getIsInTime()
    {
        return $this->isInTime;
    }

    /**
     * Set the is withdrawn
     *
     * @param string $isWithdrawn new value being set
     *
     * @return Opposition
     */
    public function setIsWithdrawn($isWithdrawn)
    {
        $this->isWithdrawn = $isWithdrawn;

        return $this;
    }

    /**
     * Get the is withdrawn
     *
     * @return string     */
    public function getIsWithdrawn()
    {
        return $this->isWithdrawn;
    }

    /**
     * Set the valid notes
     *
     * @param string $validNotes new value being set
     *
     * @return Opposition
     */
    public function setValidNotes($validNotes)
    {
        $this->validNotes = $validNotes;

        return $this;
    }

    /**
     * Get the valid notes
     *
     * @return string     */
    public function getValidNotes()
    {
        return $this->validNotes;
    }

    /**
     * Set the is willing to attend pi
     *
     * @param string $isWillingToAttendPi new value being set
     *
     * @return Opposition
     */
    public function setIsWillingToAttendPi($isWillingToAttendPi)
    {
        $this->isWillingToAttendPi = $isWillingToAttendPi;

        return $this;
    }

    /**
     * Get the is willing to attend pi
     *
     * @return string     */
    public function getIsWillingToAttendPi()
    {
        return $this->isWillingToAttendPi;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Opposition
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
     * @return Opposition
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
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return Opposition
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Set the operating centres
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $operatingCentres collection being set as the value
     *
     * @return Opposition
     */
    public function setOperatingCentres($operatingCentres)
    {
        $this->operatingCentres = $operatingCentres;

        return $this;
    }

    /**
     * Get the operating centres
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOperatingCentres()
    {
        return $this->operatingCentres;
    }

    /**
     * Add a operating centres
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $operatingCentres collection being added
     *
     * @return Opposition
     */
    public function addOperatingCentres($operatingCentres)
    {
        if ($operatingCentres instanceof ArrayCollection) {
            $this->operatingCentres = new ArrayCollection(
                array_merge(
                    $this->operatingCentres->toArray(),
                    $operatingCentres->toArray()
                )
            );
        } elseif (!$this->operatingCentres->contains($operatingCentres)) {
            $this->operatingCentres->add($operatingCentres);
        }

        return $this;
    }

    /**
     * Remove a operating centres
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $operatingCentres collection being removed
     *
     * @return Opposition
     */
    public function removeOperatingCentres($operatingCentres)
    {
        if ($this->operatingCentres->contains($operatingCentres)) {
            $this->operatingCentres->removeElement($operatingCentres);
        }

        return $this;
    }

    /**
     * Set the grounds
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $grounds collection being set as the value
     *
     * @return Opposition
     */
    public function setGrounds($grounds)
    {
        $this->grounds = $grounds;

        return $this;
    }

    /**
     * Get the grounds
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getGrounds()
    {
        return $this->grounds;
    }

    /**
     * Add a grounds
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $grounds collection being added
     *
     * @return Opposition
     */
    public function addGrounds($grounds)
    {
        if ($grounds instanceof ArrayCollection) {
            $this->grounds = new ArrayCollection(
                array_merge(
                    $this->grounds->toArray(),
                    $grounds->toArray()
                )
            );
        } elseif (!$this->grounds->contains($grounds)) {
            $this->grounds->add($grounds);
        }

        return $this;
    }

    /**
     * Remove a grounds
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $grounds collection being removed
     *
     * @return Opposition
     */
    public function removeGrounds($grounds)
    {
        if ($this->grounds->contains($grounds)) {
            $this->grounds->removeElement($grounds);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
