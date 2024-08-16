<?php

namespace Dvsa\Olcs\Api\Entity\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * CompaniesHouseInsolvencyPractitioner Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="companies_house_insolvency_practitioner",
 *    indexes={
 *        @ORM\Index(name="ix_ch_ip_companies_house_company_id",
     *     columns={"companies_house_company_id"})
 *    }
 * )
 */
abstract class AbstractCompaniesHouseInsolvencyPractitioner implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Address line1
     *
     * @var string
     *
     * @ORM\Column(type="string", name="address_line_1", length=100, nullable=true)
     */
    protected $addressLine1;

    /**
     * Address line2
     *
     * @var string
     *
     * @ORM\Column(type="string", name="address_line_2", length=100, nullable=true)
     */
    protected $addressLine2;

    /**
     * Appointed on
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="appointed_on", nullable=true)
     */
    protected $appointedOn;

    /**
     * Companies house company
     *
     * @var \Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany",
     *     fetch="LAZY",
     *     inversedBy="insolvencyPractitioners"
     * )
     * @ORM\JoinColumn(name="companies_house_company_id", referencedColumnName="id", nullable=false)
     */
    protected $companiesHouseCompany;

    /**
     * Country
     *
     * @var string
     *
     * @ORM\Column(type="string", name="country", length=32, nullable=true)
     */
    protected $country;

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
     * Locality
     *
     * @var string
     *
     * @ORM\Column(type="string", name="locality", length=100, nullable=true)
     */
    protected $locality;

    /**
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=100, nullable=true)
     */
    protected $name;

    /**
     * Postal code
     *
     * @var string
     *
     * @ORM\Column(type="string", name="postal_code", length=10, nullable=true)
     */
    protected $postalCode;

    /**
     * Region
     *
     * @var string
     *
     * @ORM\Column(type="string", name="region", length=100, nullable=true)
     */
    protected $region;

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
     * Set the address line1
     *
     * @param string $addressLine1 new value being set
     *
     * @return CompaniesHouseInsolvencyPractitioner
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
     * @return CompaniesHouseInsolvencyPractitioner
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
     * Set the appointed on
     *
     * @param \DateTime $appointedOn new value being set
     *
     * @return CompaniesHouseInsolvencyPractitioner
     */
    public function setAppointedOn($appointedOn)
    {
        $this->appointedOn = $appointedOn;

        return $this;
    }

    /**
     * Get the appointed on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getAppointedOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->appointedOn);
        }

        return $this->appointedOn;
    }

    /**
     * Set the companies house company
     *
     * @param \Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany $companiesHouseCompany entity being set as the value
     *
     * @return CompaniesHouseInsolvencyPractitioner
     */
    public function setCompaniesHouseCompany($companiesHouseCompany)
    {
        $this->companiesHouseCompany = $companiesHouseCompany;

        return $this;
    }

    /**
     * Get the companies house company
     *
     * @return \Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany
     */
    public function getCompaniesHouseCompany()
    {
        return $this->companiesHouseCompany;
    }

    /**
     * Set the country
     *
     * @param string $country new value being set
     *
     * @return CompaniesHouseInsolvencyPractitioner
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get the country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return CompaniesHouseInsolvencyPractitioner
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
     * Set the locality
     *
     * @param string $locality new value being set
     *
     * @return CompaniesHouseInsolvencyPractitioner
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * Get the locality
     *
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return CompaniesHouseInsolvencyPractitioner
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the postal code
     *
     * @param string $postalCode new value being set
     *
     * @return CompaniesHouseInsolvencyPractitioner
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get the postal code
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set the region
     *
     * @param string $region new value being set
     *
     * @return CompaniesHouseInsolvencyPractitioner
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get the region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return CompaniesHouseInsolvencyPractitioner
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
