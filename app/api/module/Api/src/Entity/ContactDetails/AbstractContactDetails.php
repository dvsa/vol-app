<?php

namespace Dvsa\Olcs\Api\Entity\ContactDetails;

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
 * ContactDetails Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="contact_details",
 *    indexes={
 *        @ORM\Index(name="ix_contact_details_address_id", columns={"address_id"}),
 *        @ORM\Index(name="ix_contact_details_contact_type", columns={"contact_type"}),
 *        @ORM\Index(name="ix_contact_details_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_contact_details_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_contact_details_person_id", columns={"person_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_contact_details_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
abstract class AbstractContactDetails implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Address
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\Address
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\Address",
     *     fetch="LAZY",
     *     cascade={"persist"},
     *     inversedBy="contactDetails"
     * )
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    protected $address;

    /**
     * Contact type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="contact_type", referencedColumnName="id", nullable=false)
     */
    protected $contactType;

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
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     */
    protected $description;

    /**
     * Email address
     *
     * @var string
     *
     * @ORM\Column(type="string", name="email_address", length=255, nullable=true)
     */
    protected $emailAddress;

    /**
     * Fao
     *
     * @var string
     *
     * @ORM\Column(type="string", name="fao", length=90, nullable=true)
     */
    protected $fao;

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
     * Person
     *
     * @var \Dvsa\Olcs\Api\Entity\Person\Person
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Person\Person",
     *     fetch="LAZY",
     *     cascade={"persist"},
     *     inversedBy="contactDetails"
     * )
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=true)
     */
    protected $person;

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
     * Written permission to engage
     *
     * @var string
     *
     * @ORM\Column(type="yesno",
     *     name="written_permission_to_engage",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $writtenPermissionToEngage = 0;

    /**
     * Phone contact
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact",
     *     mappedBy="contactDetails",
     *     cascade={"persist"},
     *     indexBy="id",
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $phoneContacts;

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
        $this->phoneContacts = new ArrayCollection();
    }

    /**
     * Set the address
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\Address $address entity being set as the value
     *
     * @return ContactDetails
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get the address
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the contact type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $contactType entity being set as the value
     *
     * @return ContactDetails
     */
    public function setContactType($contactType)
    {
        $this->contactType = $contactType;

        return $this;
    }

    /**
     * Get the contact type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getContactType()
    {
        return $this->contactType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return ContactDetails
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
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return ContactDetails
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the email address
     *
     * @param string $emailAddress new value being set
     *
     * @return ContactDetails
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Get the email address
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Set the fao
     *
     * @param string $fao new value being set
     *
     * @return ContactDetails
     */
    public function setFao($fao)
    {
        $this->fao = $fao;

        return $this;
    }

    /**
     * Get the fao
     *
     * @return string
     */
    public function getFao()
    {
        return $this->fao;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return ContactDetails
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
     * @return ContactDetails
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
     * @return ContactDetails
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
     * @return ContactDetails
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
     * Set the person
     *
     * @param \Dvsa\Olcs\Api\Entity\Person\Person $person entity being set as the value
     *
     * @return ContactDetails
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
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ContactDetails
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
     * Set the written permission to engage
     *
     * @param string $writtenPermissionToEngage new value being set
     *
     * @return ContactDetails
     */
    public function setWrittenPermissionToEngage($writtenPermissionToEngage)
    {
        $this->writtenPermissionToEngage = $writtenPermissionToEngage;

        return $this;
    }

    /**
     * Get the written permission to engage
     *
     * @return string
     */
    public function getWrittenPermissionToEngage()
    {
        return $this->writtenPermissionToEngage;
    }

    /**
     * Set the phone contact
     *
     * @param ArrayCollection $phoneContacts collection being set as the value
     *
     * @return ContactDetails
     */
    public function setPhoneContacts($phoneContacts)
    {
        $this->phoneContacts = $phoneContacts;

        return $this;
    }

    /**
     * Get the phone contacts
     *
     * @return ArrayCollection
     */
    public function getPhoneContacts()
    {
        return $this->phoneContacts;
    }

    /**
     * Add a phone contacts
     *
     * @param ArrayCollection|mixed $phoneContacts collection being added
     *
     * @return ContactDetails
     */
    public function addPhoneContacts($phoneContacts)
    {
        if ($phoneContacts instanceof ArrayCollection) {
            $this->phoneContacts = new ArrayCollection(
                array_merge(
                    $this->phoneContacts->toArray(),
                    $phoneContacts->toArray()
                )
            );
        } elseif (!$this->phoneContacts->contains($phoneContacts)) {
            $this->phoneContacts->add($phoneContacts);
        }

        return $this;
    }

    /**
     * Remove a phone contacts
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $phoneContacts collection being removed
     *
     * @return ContactDetails
     */
    public function removePhoneContacts($phoneContacts)
    {
        if ($this->phoneContacts->contains($phoneContacts)) {
            $this->phoneContacts->removeElement($phoneContacts);
        }

        return $this;
    }
}
