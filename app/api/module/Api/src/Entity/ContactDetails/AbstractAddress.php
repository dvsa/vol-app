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
 * Address Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="address",
 *    indexes={
 *        @ORM\Index(name="ix_address_admin_area", columns={"admin_area"}),
 *        @ORM\Index(name="ix_address_country_code", columns={"country_code"}),
 *        @ORM\Index(name="ix_address_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_address_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_address_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
abstract class AbstractAddress implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Address line1
     *
     * @var string
     *
     * @ORM\Column(type="string", name="saon_desc", length=90, nullable=true)
     */
    protected $addressLine1;

    /**
     * Address line2
     *
     * @var string
     *
     * @ORM\Column(type="string", name="paon_desc", length=90, nullable=true)
     */
    protected $addressLine2;

    /**
     * Address line3
     *
     * @var string
     *
     * @ORM\Column(type="string", name="street", length=100, nullable=true)
     */
    protected $addressLine3;

    /**
     * Address line4
     *
     * @var string
     *
     * @ORM\Column(type="string", name="locality", length=35, nullable=true)
     */
    protected $addressLine4;

    /**
     * Admin area
     *
     * @var \Dvsa\Olcs\Api\Entity\TrafficArea\AdminAreaTrafficArea
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\AdminAreaTrafficArea",
     *     fetch="LAZY"
     * )
     * @ORM\JoinColumn(name="admin_area", referencedColumnName="id", nullable=true)
     */
    protected $adminArea;

    /**
     * Country code
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\Country
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\Country", fetch="LAZY")
     * @ORM\JoinColumn(name="country_code", referencedColumnName="id", nullable=true)
     */
    protected $countryCode;

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
     * Olbs type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;

    /**
     * Paon end
     *
     * @var string
     *
     * @ORM\Column(type="string", name="paon_end", length=5, nullable=true)
     */
    protected $paonEnd;

    /**
     * Paon start
     *
     * @var string
     *
     * @ORM\Column(type="string", name="paon_start", length=5, nullable=true)
     */
    protected $paonStart;

    /**
     * Postcode
     *
     * @var string
     *
     * @ORM\Column(type="string", name="postcode", length=8, nullable=true)
     */
    protected $postcode;

    /**
     * Saon end
     *
     * @var string
     *
     * @ORM\Column(type="string", name="saon_end", length=5, nullable=true)
     */
    protected $saonEnd;

    /**
     * Saon start
     *
     * @var string
     *
     * @ORM\Column(type="string", name="saon_start", length=5, nullable=true)
     */
    protected $saonStart;

    /**
     * Town
     *
     * @var string
     *
     * @ORM\Column(type="string", name="town", length=30, nullable=true)
     */
    protected $town;

    /**
     * Uprn
     *
     * @var int
     *
     * @ORM\Column(type="bigint", name="uprn", nullable=true)
     */
    protected $uprn;

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
     * Contact detail
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails",
     *     mappedBy="address"
     * )
     */
    protected $contactDetails;

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
        $this->contactDetails = new ArrayCollection();
    }

    /**
     * Set the address line1
     *
     * @param string $addressLine1 new value being set
     *
     * @return Address
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * Get the address line1
     *
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * Set the address line2
     *
     * @param string $addressLine2 new value being set
     *
     * @return Address
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * Get the address line2
     *
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * Set the address line3
     *
     * @param string $addressLine3 new value being set
     *
     * @return Address
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = $addressLine3;

        return $this;
    }

    /**
     * Get the address line3
     *
     * @return string
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * Set the address line4
     *
     * @param string $addressLine4 new value being set
     *
     * @return Address
     */
    public function setAddressLine4($addressLine4)
    {
        $this->addressLine4 = $addressLine4;

        return $this;
    }

    /**
     * Get the address line4
     *
     * @return string
     */
    public function getAddressLine4()
    {
        return $this->addressLine4;
    }

    /**
     * Set the admin area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\AdminAreaTrafficArea $adminArea entity being set as the value
     *
     * @return Address
     */
    public function setAdminArea($adminArea)
    {
        $this->adminArea = $adminArea;

        return $this;
    }

    /**
     * Get the admin area
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\AdminAreaTrafficArea
     */
    public function getAdminArea()
    {
        return $this->adminArea;
    }

    /**
     * Set the country code
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\Country $countryCode entity being set as the value
     *
     * @return Address
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get the country code
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\Country
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Address
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
     * @return Address
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
     * @return Address
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
     * @return Address
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
     * @return Address
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
     * Set the paon end
     *
     * @param string $paonEnd new value being set
     *
     * @return Address
     */
    public function setPaonEnd($paonEnd)
    {
        $this->paonEnd = $paonEnd;

        return $this;
    }

    /**
     * Get the paon end
     *
     * @return string
     */
    public function getPaonEnd()
    {
        return $this->paonEnd;
    }

    /**
     * Set the paon start
     *
     * @param string $paonStart new value being set
     *
     * @return Address
     */
    public function setPaonStart($paonStart)
    {
        $this->paonStart = $paonStart;

        return $this;
    }

    /**
     * Get the paon start
     *
     * @return string
     */
    public function getPaonStart()
    {
        return $this->paonStart;
    }

    /**
     * Set the postcode
     *
     * @param string $postcode new value being set
     *
     * @return Address
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get the postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set the saon end
     *
     * @param string $saonEnd new value being set
     *
     * @return Address
     */
    public function setSaonEnd($saonEnd)
    {
        $this->saonEnd = $saonEnd;

        return $this;
    }

    /**
     * Get the saon end
     *
     * @return string
     */
    public function getSaonEnd()
    {
        return $this->saonEnd;
    }

    /**
     * Set the saon start
     *
     * @param string $saonStart new value being set
     *
     * @return Address
     */
    public function setSaonStart($saonStart)
    {
        $this->saonStart = $saonStart;

        return $this;
    }

    /**
     * Get the saon start
     *
     * @return string
     */
    public function getSaonStart()
    {
        return $this->saonStart;
    }

    /**
     * Set the town
     *
     * @param string $town new value being set
     *
     * @return Address
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get the town
     *
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set the uprn
     *
     * @param int $uprn new value being set
     *
     * @return Address
     */
    public function setUprn($uprn)
    {
        $this->uprn = $uprn;

        return $this;
    }

    /**
     * Get the uprn
     *
     * @return int
     */
    public function getUprn()
    {
        return $this->uprn;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Address
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
     * Set the contact detail
     *
     * @param ArrayCollection $contactDetails collection being set as the value
     *
     * @return Address
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
     * @return Address
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
     * @return Address
     */
    public function removeContactDetails($contactDetails)
    {
        if ($this->contactDetails->contains($contactDetails)) {
            $this->contactDetails->removeElement($contactDetails);
        }

        return $this;
    }
}
