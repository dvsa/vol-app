<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

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
 * Disqualification Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="disqualification",
 *    indexes={
 *        @ORM\Index(name="ix_disqualification_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_disqualification_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_disqualification_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_disqualification_person_id", columns={"person_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_disqualification_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractDisqualification implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Is disqualified
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_disqualified", nullable=true)
     */
    protected $isDisqualified;

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
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Olbs type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=20, nullable=true)
     */
    protected $olbsType;

    /**
     * Organisation
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Organisation",
     *     fetch="LAZY",
     *     inversedBy="disqualifications"
     * )
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    protected $organisation;

    /**
     * Period
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="period", nullable=true)
     */
    protected $period;

    /**
     * Person
     *
     * @var \Dvsa\Olcs\Api\Entity\Person\Person
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Person\Person",
     *     fetch="LAZY",
     *     inversedBy="disqualifications"
     * )
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=true)
     */
    protected $person;

    /**
     * Start date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="start_date", nullable=true)
     */
    protected $startDate;

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
     * @return Disqualification
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
     * @return Disqualification
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
     * Set the is disqualified
     *
     * @param string $isDisqualified new value being set
     *
     * @return Disqualification
     */
    public function setIsDisqualified($isDisqualified)
    {
        $this->isDisqualified = $isDisqualified;

        return $this;
    }

    /**
     * Get the is disqualified
     *
     * @return string
     */
    public function getIsDisqualified()
    {
        return $this->isDisqualified;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return Disqualification
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
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return Disqualification
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get the notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return Disqualification
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
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return Disqualification
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string
     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Set the organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation entity being set as the value
     *
     * @return Disqualification
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get the organisation
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set the period
     *
     * @param int $period new value being set
     *
     * @return Disqualification
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Get the period
     *
     * @return int
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Set the person
     *
     * @param \Dvsa\Olcs\Api\Entity\Person\Person $person entity being set as the value
     *
     * @return Disqualification
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get the person
     *
     * @return \Dvsa\Olcs\Api\Entity\Person\Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Set the start date
     *
     * @param \DateTime $startDate new value being set
     *
     * @return Disqualification
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get the start date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getStartDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->startDate);
        }

        return $this->startDate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Disqualification
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
