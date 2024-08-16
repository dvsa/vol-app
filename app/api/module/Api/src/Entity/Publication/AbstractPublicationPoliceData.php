<?php

namespace Dvsa\Olcs\Api\Entity\Publication;

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
 * PublicationPoliceData Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="publication_police_data",
 *    indexes={
 *        @ORM\Index(name="ix_publication_police_data_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_publication_police_data_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_publication_police_data_person_id", columns={"person_id"}),
 *        @ORM\Index(name="ix_publication_police_data_publication_link_id",
     *     columns={"publication_link_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_publication_police_data_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractPublicationPoliceData implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Birth date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="birth_date", nullable=true)
     */
    protected $birthDate;

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
     * Olbs dob
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_dob", length=20, nullable=true)
     */
    protected $olbsDob;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Person
     *
     * @var \Dvsa\Olcs\Api\Entity\Person\Person
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Person\Person", fetch="LAZY")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=true)
     */
    protected $person;

    /**
     * Publication link
     *
     * @var \Dvsa\Olcs\Api\Entity\Publication\PublicationLink
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Publication\PublicationLink",
     *     fetch="LAZY",
     *     inversedBy="policeDatas"
     * )
     * @ORM\JoinColumn(name="publication_link_id", referencedColumnName="id", nullable=false)
     */
    protected $publicationLink;

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
     * Set the birth date
     *
     * @param \DateTime $birthDate new value being set
     *
     * @return PublicationPoliceData
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return PublicationPoliceData
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
     * @return PublicationPoliceData
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
     * @return PublicationPoliceData
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
     * @return PublicationPoliceData
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
     * @return PublicationPoliceData
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
     * Set the olbs dob
     *
     * @param string $olbsDob new value being set
     *
     * @return PublicationPoliceData
     */
    public function setOlbsDob($olbsDob)
    {
        $this->olbsDob = $olbsDob;

        return $this;
    }

    /**
     * Get the olbs dob
     *
     * @return string
     */
    public function getOlbsDob()
    {
        return $this->olbsDob;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return PublicationPoliceData
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
     * Set the person
     *
     * @param \Dvsa\Olcs\Api\Entity\Person\Person $person entity being set as the value
     *
     * @return PublicationPoliceData
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
     * Set the publication link
     *
     * @param \Dvsa\Olcs\Api\Entity\Publication\PublicationLink $publicationLink entity being set as the value
     *
     * @return PublicationPoliceData
     */
    public function setPublicationLink($publicationLink)
    {
        $this->publicationLink = $publicationLink;

        return $this;
    }

    /**
     * Get the publication link
     *
     * @return \Dvsa\Olcs\Api\Entity\Publication\PublicationLink
     */
    public function getPublicationLink()
    {
        return $this->publicationLink;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return PublicationPoliceData
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
