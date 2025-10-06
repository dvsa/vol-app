<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\CompaniesHouse;

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

/**
 * AbstractCompaniesHouseOfficer Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="companies_house_officer",
 *    indexes={
 *        @ORM\Index(name="ix_companies_house_officer_companies_house_company_id", columns={"companies_house_company_id"})
 *    }
 * )
 */
abstract class AbstractCompaniesHouseOfficer implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Foreign Key to companies_house_company
     *
     * @var \Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany", fetch="LAZY")
     * @ORM\JoinColumn(name="companies_house_company_id", referencedColumnName="id")
     */
    protected $companiesHouseCompany;

    /**
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=100, nullable=true)
     */
    protected $name;

    /**
     * Role
     *
     * @var string
     *
     * @ORM\Column(type="string", name="role", length=64, nullable=true)
     */
    protected $role;

    /**
     * Date of birth
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="date_of_birth", nullable=true)
     */
    protected $dateOfBirth;

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
     * @return CompaniesHouseOfficer
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
     * Set the companies house company
     *
     * @param \Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany $companiesHouseCompany new value being set
     *
     * @return CompaniesHouseOfficer
     */
    public function setCompaniesHouseCompany($companiesHouseCompany)
    {
        $this->companiesHouseCompany = $companiesHouseCompany;

        return $this;
    }

    /**
     * Get the companies house company
     *
     * @return \Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany     */
    public function getCompaniesHouseCompany()
    {
        return $this->companiesHouseCompany;
    }

    /**
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return CompaniesHouseOfficer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the role
     *
     * @param string $role new value being set
     *
     * @return CompaniesHouseOfficer
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the role
     *
     * @return string     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the date of birth
     *
     * @param \DateTime $dateOfBirth new value being set
     *
     * @return CompaniesHouseOfficer
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * Get the date of birth
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDateOfBirth($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->dateOfBirth);
        }

        return $this->dateOfBirth;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return CompaniesHouseOfficer
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