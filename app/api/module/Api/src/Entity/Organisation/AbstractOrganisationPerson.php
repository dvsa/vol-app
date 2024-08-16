<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * OrganisationPerson Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="organisation_person",
 *    indexes={
 *        @ORM\Index(name="ix_organisation_person_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_organisation_person_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_organisation_person_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_organisation_person_person_id", columns={"person_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_organisation_person_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractOrganisationPerson implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

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
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Organisation
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Organisation",
     *     fetch="LAZY",
     *     inversedBy="organisationPersons"
     * )
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=false)
     */
    protected $organisation;

    /**
     * Person
     *
     * @var \Dvsa\Olcs\Api\Entity\Person\Person
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Person\Person",
     *     fetch="LAZY",
     *     inversedBy="organisationPersons"
     * )
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     */
    protected $person;

    /**
     * Position
     *
     * @var string
     *
     * @ORM\Column(type="string", name="position", length=45, nullable=true)
     */
    protected $position;

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
     * @return OrganisationPerson
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
     * @return OrganisationPerson
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
     * @return OrganisationPerson
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
     * @return OrganisationPerson
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
     * Set the organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation entity being set as the value
     *
     * @return OrganisationPerson
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
     * Set the person
     *
     * @param \Dvsa\Olcs\Api\Entity\Person\Person $person entity being set as the value
     *
     * @return OrganisationPerson
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
     * Set the position
     *
     * @param string $position new value being set
     *
     * @return OrganisationPerson
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get the position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return OrganisationPerson
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
