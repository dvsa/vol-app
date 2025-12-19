<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Licence;

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
 * AbstractLicenceStatusRule Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="licence_status_rule",
 *    indexes={
 *        @ORM\Index(name="ix_licence_status_rule_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_licence_status_rule_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_licence_status_rule_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_licence_status_rule_licence_status", columns={"licence_status"})
 *    }
 * )
 */
abstract class AbstractLicenceStatusRule implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id")
     */
    protected $licence;

    /**
     * The status the licence will inherit on the start date
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_status", referencedColumnName="id")
     */
    protected $licenceStatus;

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
     * Start date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="start_date", nullable=false)
     */
    protected $startDate;

    /**
     * End date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="end_date", nullable=true)
     */
    protected $endDate;

    /**
     * Date processed by batch job
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="start_processed_date", nullable=true)
     */
    protected $startProcessedDate;

    /**
     * End processed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="end_processed_date", nullable=true)
     */
    protected $endProcessedDate;

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
     * @return LicenceStatusRule
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
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return LicenceStatusRule
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the licence status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $licenceStatus new value being set
     *
     * @return LicenceStatusRule
     */
    public function setLicenceStatus($licenceStatus)
    {
        $this->licenceStatus = $licenceStatus;

        return $this;
    }

    /**
     * Get the licence status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getLicenceStatus()
    {
        return $this->licenceStatus;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LicenceStatusRule
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
     * @return LicenceStatusRule
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
     * Set the start date
     *
     * @param \DateTime $startDate new value being set
     *
     * @return LicenceStatusRule
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
     * @return \DateTime     */
    public function getStartDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->startDate);
        }

        return $this->startDate;
    }

    /**
     * Set the end date
     *
     * @param \DateTime $endDate new value being set
     *
     * @return LicenceStatusRule
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get the end date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getEndDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->endDate);
        }

        return $this->endDate;
    }

    /**
     * Set the start processed date
     *
     * @param \DateTime $startProcessedDate new value being set
     *
     * @return LicenceStatusRule
     */
    public function setStartProcessedDate($startProcessedDate)
    {
        $this->startProcessedDate = $startProcessedDate;

        return $this;
    }

    /**
     * Get the start processed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getStartProcessedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->startProcessedDate);
        }

        return $this->startProcessedDate;
    }

    /**
     * Set the end processed date
     *
     * @param \DateTime $endProcessedDate new value being set
     *
     * @return LicenceStatusRule
     */
    public function setEndProcessedDate($endProcessedDate)
    {
        $this->endProcessedDate = $endProcessedDate;

        return $this;
    }

    /**
     * Get the end processed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getEndProcessedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->endProcessedDate);
        }

        return $this->endProcessedDate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LicenceStatusRule
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
     * @return LicenceStatusRule
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
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}