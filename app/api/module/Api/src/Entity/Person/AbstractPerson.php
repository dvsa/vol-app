<?php

namespace Dvsa\Olcs\Api\Entity\Person;

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
 * Person Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="person",
 *    indexes={
 *        @ORM\Index(name="ix_person_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_person_family_name", columns={"family_name"}),
 *        @ORM\Index(name="ix_person_forename", columns={"forename"}),
 *        @ORM\Index(name="ix_person_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_person_title", columns={"title"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_person_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
abstract class AbstractPerson implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Birth date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="birth_date", nullable=true)
     */
    protected $birthDate;

    /**
     * Birth place
     *
     * @var string
     *
     * @ORM\Column(type="string", name="birth_place", length=50, nullable=true)
     */
    protected $birthPlace;

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
     * Family name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="family_name", length=35, nullable=true)
     */
    protected $familyName;

    /**
     * Forename
     *
     * @var string
     *
     * @ORM\Column(type="string", name="forename", length=35, nullable=true)
     */
    protected $forename;

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
     * Olbs type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;

    /**
     * Other name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="other_name", length=35, nullable=true)
     */
    protected $otherName;

    /**
     * Title
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="title", referencedColumnName="id", nullable=true)
     */
    protected $title;

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
     * Application organisation person
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson",
     *     mappedBy="person"
     * )
     */
    protected $applicationOrganisationPersons;

    /**
     * Contact detail
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails",
     *     mappedBy="person"
     * )
     */
    protected $contactDetails;

    /**
     * Disqualification
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Disqualification",
     *     mappedBy="person"
     * )
     */
    protected $disqualifications;

    /**
     * Organisation person
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson",
     *     mappedBy="person"
     * )
     */
    protected $organisationPersons;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->applicationOrganisationPersons = new ArrayCollection();
        $this->contactDetails = new ArrayCollection();
        $this->disqualifications = new ArrayCollection();
        $this->organisationPersons = new ArrayCollection();
    }

    /**
     * Set the birth date
     *
     * @param \DateTime $birthDate new value being set
     *
     * @return Person
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
     * @return \DateTime|string

     */
    public function getBirthDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->birthDate);
        }

        return $this->birthDate;
    }

    /**
     * Set the birth place
     *
     * @param string $birthPlace new value being set
     *
     * @return Person
     */
    public function setBirthPlace($birthPlace)
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    /**
     * Get the birth place
     *
     * @return string
     */
    public function getBirthPlace()
    {
        return $this->birthPlace;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Person
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
     * Set the family name
     *
     * @param string $familyName new value being set
     *
     * @return Person
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * Get the family name
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Set the forename
     *
     * @param string $forename new value being set
     *
     * @return Person
     */
    public function setForename($forename)
    {
        $this->forename = $forename;

        return $this;
    }

    /**
     * Get the forename
     *
     * @return string
     */
    public function getForename()
    {
        return $this->forename;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Person
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
     * @return Person
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
     * @return Person
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
     * @return Person
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
     * Set the other name
     *
     * @param string $otherName new value being set
     *
     * @return Person
     */
    public function setOtherName($otherName)
    {
        $this->otherName = $otherName;

        return $this;
    }

    /**
     * Get the other name
     *
     * @return string
     */
    public function getOtherName()
    {
        return $this->otherName;
    }

    /**
     * Set the title
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $title entity being set as the value
     *
     * @return Person
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the title
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Person
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

    /**
     * Set the application organisation person
     *
     * @param ArrayCollection $applicationOrganisationPersons collection being set as the value
     *
     * @return Person
     */
    public function setApplicationOrganisationPersons($applicationOrganisationPersons)
    {
        $this->applicationOrganisationPersons = $applicationOrganisationPersons;

        return $this;
    }

    /**
     * Get the application organisation persons
     *
     * @return ArrayCollection
     */
    public function getApplicationOrganisationPersons()
    {
        return $this->applicationOrganisationPersons;
    }

    /**
     * Add a application organisation persons
     *
     * @param ArrayCollection|mixed $applicationOrganisationPersons collection being added
     *
     * @return Person
     */
    public function addApplicationOrganisationPersons($applicationOrganisationPersons)
    {
        if ($applicationOrganisationPersons instanceof ArrayCollection) {
            $this->applicationOrganisationPersons = new ArrayCollection(
                array_merge(
                    $this->applicationOrganisationPersons->toArray(),
                    $applicationOrganisationPersons->toArray()
                )
            );
        } elseif (!$this->applicationOrganisationPersons->contains($applicationOrganisationPersons)) {
            $this->applicationOrganisationPersons->add($applicationOrganisationPersons);
        }

        return $this;
    }

    /**
     * Remove a application organisation persons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applicationOrganisationPersons collection being removed
     *
     * @return Person
     */
    public function removeApplicationOrganisationPersons($applicationOrganisationPersons)
    {
        if ($this->applicationOrganisationPersons->contains($applicationOrganisationPersons)) {
            $this->applicationOrganisationPersons->removeElement($applicationOrganisationPersons);
        }

        return $this;
    }

    /**
     * Set the contact detail
     *
     * @param ArrayCollection $contactDetails collection being set as the value
     *
     * @return Person
     */
    public function setContactDetails($contactDetails)
    {
        $this->contactDetails = $contactDetails;

        return $this;
    }

    /**
     * Get the contact details
     *
     * @return ArrayCollection
     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    /**
     * Add a contact details
     *
     * @param ArrayCollection|mixed $contactDetails collection being added
     *
     * @return Person
     */
    public function addContactDetails($contactDetails)
    {
        if ($contactDetails instanceof ArrayCollection) {
            $this->contactDetails = new ArrayCollection(
                array_merge(
                    $this->contactDetails->toArray(),
                    $contactDetails->toArray()
                )
            );
        } elseif (!$this->contactDetails->contains($contactDetails)) {
            $this->contactDetails->add($contactDetails);
        }

        return $this;
    }

    /**
     * Remove a contact details
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $contactDetails collection being removed
     *
     * @return Person
     */
    public function removeContactDetails($contactDetails)
    {
        if ($this->contactDetails->contains($contactDetails)) {
            $this->contactDetails->removeElement($contactDetails);
        }

        return $this;
    }

    /**
     * Set the disqualification
     *
     * @param ArrayCollection $disqualifications collection being set as the value
     *
     * @return Person
     */
    public function setDisqualifications($disqualifications)
    {
        $this->disqualifications = $disqualifications;

        return $this;
    }

    /**
     * Get the disqualifications
     *
     * @return ArrayCollection
     */
    public function getDisqualifications()
    {
        return $this->disqualifications;
    }

    /**
     * Add a disqualifications
     *
     * @param ArrayCollection|mixed $disqualifications collection being added
     *
     * @return Person
     */
    public function addDisqualifications($disqualifications)
    {
        if ($disqualifications instanceof ArrayCollection) {
            $this->disqualifications = new ArrayCollection(
                array_merge(
                    $this->disqualifications->toArray(),
                    $disqualifications->toArray()
                )
            );
        } elseif (!$this->disqualifications->contains($disqualifications)) {
            $this->disqualifications->add($disqualifications);
        }

        return $this;
    }

    /**
     * Remove a disqualifications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $disqualifications collection being removed
     *
     * @return Person
     */
    public function removeDisqualifications($disqualifications)
    {
        if ($this->disqualifications->contains($disqualifications)) {
            $this->disqualifications->removeElement($disqualifications);
        }

        return $this;
    }

    /**
     * Set the organisation person
     *
     * @param ArrayCollection $organisationPersons collection being set as the value
     *
     * @return Person
     */
    public function setOrganisationPersons($organisationPersons)
    {
        $this->organisationPersons = $organisationPersons;

        return $this;
    }

    /**
     * Get the organisation persons
     *
     * @return ArrayCollection
     */
    public function getOrganisationPersons()
    {
        return $this->organisationPersons;
    }

    /**
     * Add a organisation persons
     *
     * @param ArrayCollection|mixed $organisationPersons collection being added
     *
     * @return Person
     */
    public function addOrganisationPersons($organisationPersons)
    {
        if ($organisationPersons instanceof ArrayCollection) {
            $this->organisationPersons = new ArrayCollection(
                array_merge(
                    $this->organisationPersons->toArray(),
                    $organisationPersons->toArray()
                )
            );
        } elseif (!$this->organisationPersons->contains($organisationPersons)) {
            $this->organisationPersons->add($organisationPersons);
        }

        return $this;
    }

    /**
     * Remove a organisation persons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $organisationPersons collection being removed
     *
     * @return Person
     */
    public function removeOrganisationPersons($organisationPersons)
    {
        if ($this->organisationPersons->contains($organisationPersons)) {
            $this->organisationPersons->removeElement($organisationPersons);
        }

        return $this;
    }
}
