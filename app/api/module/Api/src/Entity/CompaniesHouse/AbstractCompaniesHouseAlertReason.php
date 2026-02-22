<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AbstractCompaniesHouseAlertReason Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="companies_house_alert_reason",
 *    indexes={
 *        @ORM\Index(name="ix_companies_house_alert_reason_companies_house_alert_id", columns={"companies_house_alert_id"}),
 *        @ORM\Index(name="ix_companies_house_alert_reason_reason_type", columns={"reason_type"})
 *    }
 * )
 */
abstract class AbstractCompaniesHouseAlertReason implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;

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
     * Foreign Key to companies_house_alert
     *
     * @var \Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert", fetch="LAZY")
     * @ORM\JoinColumn(name="companies_house_alert_id", referencedColumnName="id")
     */
    protected $companiesHouseAlert;

    /**
     * ReasonType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="reason_type", referencedColumnName="id", nullable=true)
     */
    protected $reasonType;

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
     * @return CompaniesHouseAlertReason
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
     * Set the companies house alert
     *
     * @param \Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert $companiesHouseAlert new value being set
     *
     * @return CompaniesHouseAlertReason
     */
    public function setCompaniesHouseAlert($companiesHouseAlert)
    {
        $this->companiesHouseAlert = $companiesHouseAlert;

        return $this;
    }

    /**
     * Get the companies house alert
     *
     * @return \Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert     */
    public function getCompaniesHouseAlert()
    {
        return $this->companiesHouseAlert;
    }

    /**
     * Set the reason type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $reasonType new value being set
     *
     * @return CompaniesHouseAlertReason
     */
    public function setReasonType($reasonType)
    {
        $this->reasonType = $reasonType;

        return $this;
    }

    /**
     * Get the reason type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getReasonType()
    {
        return $this->reasonType;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return CompaniesHouseAlertReason
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
