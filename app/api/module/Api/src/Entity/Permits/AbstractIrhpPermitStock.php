<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Permits;

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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractIrhpPermitStock Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irhp_permit_stock",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_permit_stock_application_path_group_id", columns={"application_path_group_id"}),
 *        @ORM\Index(name="fk_irhp_permit_stock_business_process_ref_data_id", columns={"business_process"}),
 *        @ORM\Index(name="fk_irhp_permit_stock_country_id", columns={"country_id"}),
 *        @ORM\Index(name="fk_irhp_permit_stock_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_permit_stock_irhp_permit_types1_idx", columns={"irhp_permit_type_id"}),
 *        @ORM\Index(name="fk_irhp_permit_stock_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_irhp_permit_stock_permit_category_ref_data_id", columns={"permit_category"}),
 *        @ORM\Index(name="ix_irhp_permit_stock_status", columns={"status"})
 *    }
 * )
 */
abstract class AbstractIrhpPermitStock implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * IrhpPermitType
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_permit_type_id", referencedColumnName="id")
     */
    protected $irhpPermitType;

    /**
     * ApplicationPathGroup
     *
     * @var \Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup", fetch="LAZY")
     * @ORM\JoinColumn(name="application_path_group_id", referencedColumnName="id", nullable=true)
     */
    protected $applicationPathGroup;

    /**
     * BusinessProcess
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="business_process", referencedColumnName="id", nullable=true)
     */
    protected $businessProcess;

    /**
     * Country
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\Country
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\Country", fetch="LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected $country;

    /**
     * PermitCategory
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="permit_category", referencedColumnName="id", nullable=true)
     */
    protected $permitCategory;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=true)
     */
    protected $status;

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
     * Period name key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="period_name_key", length=512, nullable=true)
     */
    protected $periodNameKey;

    /**
     * Valid from
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="valid_from", nullable=true)
     */
    protected $validFrom;

    /**
     * Valid to
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="valid_to", nullable=true)
     */
    protected $validTo;

    /**
     * Initial stock
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="initial_stock", nullable=true)
     */
    protected $initialStock;

    /**
     * Hidden ss
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="hidden_ss", nullable=false, options={"default": 0})
     */
    protected $hiddenSs = 0;

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
     * IrhpPermitJurisdictionQuotas
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitJurisdictionQuota", mappedBy="irhpPermitStock")
     */
    protected $irhpPermitJurisdictionQuotas;

    /**
     * IrhpPermitRanges
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange", mappedBy="irhpPermitStock")
     */
    protected $irhpPermitRanges;

    /**
     * IrhpPermitSectorQuotas
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota", mappedBy="irhpPermitStock")
     */
    protected $irhpPermitSectorQuotas;

    /**
     * IrhpPermitWindows
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow", mappedBy="irhpPermitStock")
     */
    protected $irhpPermitWindows;

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
        $this->irhpPermitJurisdictionQuotas = new ArrayCollection();
        $this->irhpPermitRanges = new ArrayCollection();
        $this->irhpPermitSectorQuotas = new ArrayCollection();
        $this->irhpPermitWindows = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return IrhpPermitStock
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
     * Set the irhp permit type
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType $irhpPermitType new value being set
     *
     * @return IrhpPermitStock
     */
    public function setIrhpPermitType($irhpPermitType)
    {
        $this->irhpPermitType = $irhpPermitType;

        return $this;
    }

    /**
     * Get the irhp permit type
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType     */
    public function getIrhpPermitType()
    {
        return $this->irhpPermitType;
    }

    /**
     * Set the application path group
     *
     * @param \Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup $applicationPathGroup new value being set
     *
     * @return IrhpPermitStock
     */
    public function setApplicationPathGroup($applicationPathGroup)
    {
        $this->applicationPathGroup = $applicationPathGroup;

        return $this;
    }

    /**
     * Get the application path group
     *
     * @return \Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup     */
    public function getApplicationPathGroup()
    {
        return $this->applicationPathGroup;
    }

    /**
     * Set the business process
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $businessProcess new value being set
     *
     * @return IrhpPermitStock
     */
    public function setBusinessProcess($businessProcess)
    {
        $this->businessProcess = $businessProcess;

        return $this;
    }

    /**
     * Get the business process
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getBusinessProcess()
    {
        return $this->businessProcess;
    }

    /**
     * Set the country
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\Country $country new value being set
     *
     * @return IrhpPermitStock
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get the country
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\Country     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set the permit category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $permitCategory new value being set
     *
     * @return IrhpPermitStock
     */
    public function setPermitCategory($permitCategory)
    {
        $this->permitCategory = $permitCategory;

        return $this;
    }

    /**
     * Get the permit category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getPermitCategory()
    {
        return $this->permitCategory;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status new value being set
     *
     * @return IrhpPermitStock
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return IrhpPermitStock
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
     * @return IrhpPermitStock
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
     * Set the period name key
     *
     * @param string $periodNameKey new value being set
     *
     * @return IrhpPermitStock
     */
    public function setPeriodNameKey($periodNameKey)
    {
        $this->periodNameKey = $periodNameKey;

        return $this;
    }

    /**
     * Get the period name key
     *
     * @return string     */
    public function getPeriodNameKey()
    {
        return $this->periodNameKey;
    }

    /**
     * Set the valid from
     *
     * @param \DateTime $validFrom new value being set
     *
     * @return IrhpPermitStock
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Get the valid from
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getValidFrom($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->validFrom);
        }

        return $this->validFrom;
    }

    /**
     * Set the valid to
     *
     * @param \DateTime $validTo new value being set
     *
     * @return IrhpPermitStock
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;

        return $this;
    }

    /**
     * Get the valid to
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getValidTo($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->validTo);
        }

        return $this->validTo;
    }

    /**
     * Set the initial stock
     *
     * @param int $initialStock new value being set
     *
     * @return IrhpPermitStock
     */
    public function setInitialStock($initialStock)
    {
        $this->initialStock = $initialStock;

        return $this;
    }

    /**
     * Get the initial stock
     *
     * @return int     */
    public function getInitialStock()
    {
        return $this->initialStock;
    }

    /**
     * Set the hidden ss
     *
     * @param bool $hiddenSs new value being set
     *
     * @return IrhpPermitStock
     */
    public function setHiddenSs($hiddenSs)
    {
        $this->hiddenSs = $hiddenSs;

        return $this;
    }

    /**
     * Get the hidden ss
     *
     * @return bool     */
    public function getHiddenSs()
    {
        return $this->hiddenSs;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrhpPermitStock
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
     * Set the irhp permit jurisdiction quotas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitJurisdictionQuotas collection being set as the value
     *
     * @return IrhpPermitStock
     */
    public function setIrhpPermitJurisdictionQuotas($irhpPermitJurisdictionQuotas)
    {
        $this->irhpPermitJurisdictionQuotas = $irhpPermitJurisdictionQuotas;

        return $this;
    }

    /**
     * Get the irhp permit jurisdiction quotas
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitJurisdictionQuotas()
    {
        return $this->irhpPermitJurisdictionQuotas;
    }

    /**
     * Add a irhp permit jurisdiction quotas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitJurisdictionQuotas collection being added
     *
     * @return IrhpPermitStock
     */
    public function addIrhpPermitJurisdictionQuotas($irhpPermitJurisdictionQuotas)
    {
        if ($irhpPermitJurisdictionQuotas instanceof ArrayCollection) {
            $this->irhpPermitJurisdictionQuotas = new ArrayCollection(
                array_merge(
                    $this->irhpPermitJurisdictionQuotas->toArray(),
                    $irhpPermitJurisdictionQuotas->toArray()
                )
            );
        } elseif (!$this->irhpPermitJurisdictionQuotas->contains($irhpPermitJurisdictionQuotas)) {
            $this->irhpPermitJurisdictionQuotas->add($irhpPermitJurisdictionQuotas);
        }

        return $this;
    }

    /**
     * Remove a irhp permit jurisdiction quotas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitJurisdictionQuotas collection being removed
     *
     * @return IrhpPermitStock
     */
    public function removeIrhpPermitJurisdictionQuotas($irhpPermitJurisdictionQuotas)
    {
        if ($this->irhpPermitJurisdictionQuotas->contains($irhpPermitJurisdictionQuotas)) {
            $this->irhpPermitJurisdictionQuotas->removeElement($irhpPermitJurisdictionQuotas);
        }

        return $this;
    }

    /**
     * Set the irhp permit ranges
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRanges collection being set as the value
     *
     * @return IrhpPermitStock
     */
    public function setIrhpPermitRanges($irhpPermitRanges)
    {
        $this->irhpPermitRanges = $irhpPermitRanges;

        return $this;
    }

    /**
     * Get the irhp permit ranges
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitRanges()
    {
        return $this->irhpPermitRanges;
    }

    /**
     * Add a irhp permit ranges
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitRanges collection being added
     *
     * @return IrhpPermitStock
     */
    public function addIrhpPermitRanges($irhpPermitRanges)
    {
        if ($irhpPermitRanges instanceof ArrayCollection) {
            $this->irhpPermitRanges = new ArrayCollection(
                array_merge(
                    $this->irhpPermitRanges->toArray(),
                    $irhpPermitRanges->toArray()
                )
            );
        } elseif (!$this->irhpPermitRanges->contains($irhpPermitRanges)) {
            $this->irhpPermitRanges->add($irhpPermitRanges);
        }

        return $this;
    }

    /**
     * Remove a irhp permit ranges
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRanges collection being removed
     *
     * @return IrhpPermitStock
     */
    public function removeIrhpPermitRanges($irhpPermitRanges)
    {
        if ($this->irhpPermitRanges->contains($irhpPermitRanges)) {
            $this->irhpPermitRanges->removeElement($irhpPermitRanges);
        }

        return $this;
    }

    /**
     * Set the irhp permit sector quotas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitSectorQuotas collection being set as the value
     *
     * @return IrhpPermitStock
     */
    public function setIrhpPermitSectorQuotas($irhpPermitSectorQuotas)
    {
        $this->irhpPermitSectorQuotas = $irhpPermitSectorQuotas;

        return $this;
    }

    /**
     * Get the irhp permit sector quotas
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitSectorQuotas()
    {
        return $this->irhpPermitSectorQuotas;
    }

    /**
     * Add a irhp permit sector quotas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitSectorQuotas collection being added
     *
     * @return IrhpPermitStock
     */
    public function addIrhpPermitSectorQuotas($irhpPermitSectorQuotas)
    {
        if ($irhpPermitSectorQuotas instanceof ArrayCollection) {
            $this->irhpPermitSectorQuotas = new ArrayCollection(
                array_merge(
                    $this->irhpPermitSectorQuotas->toArray(),
                    $irhpPermitSectorQuotas->toArray()
                )
            );
        } elseif (!$this->irhpPermitSectorQuotas->contains($irhpPermitSectorQuotas)) {
            $this->irhpPermitSectorQuotas->add($irhpPermitSectorQuotas);
        }

        return $this;
    }

    /**
     * Remove a irhp permit sector quotas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitSectorQuotas collection being removed
     *
     * @return IrhpPermitStock
     */
    public function removeIrhpPermitSectorQuotas($irhpPermitSectorQuotas)
    {
        if ($this->irhpPermitSectorQuotas->contains($irhpPermitSectorQuotas)) {
            $this->irhpPermitSectorQuotas->removeElement($irhpPermitSectorQuotas);
        }

        return $this;
    }

    /**
     * Set the irhp permit windows
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitWindows collection being set as the value
     *
     * @return IrhpPermitStock
     */
    public function setIrhpPermitWindows($irhpPermitWindows)
    {
        $this->irhpPermitWindows = $irhpPermitWindows;

        return $this;
    }

    /**
     * Get the irhp permit windows
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitWindows()
    {
        return $this->irhpPermitWindows;
    }

    /**
     * Add a irhp permit windows
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitWindows collection being added
     *
     * @return IrhpPermitStock
     */
    public function addIrhpPermitWindows($irhpPermitWindows)
    {
        if ($irhpPermitWindows instanceof ArrayCollection) {
            $this->irhpPermitWindows = new ArrayCollection(
                array_merge(
                    $this->irhpPermitWindows->toArray(),
                    $irhpPermitWindows->toArray()
                )
            );
        } elseif (!$this->irhpPermitWindows->contains($irhpPermitWindows)) {
            $this->irhpPermitWindows->add($irhpPermitWindows);
        }

        return $this;
    }

    /**
     * Remove a irhp permit windows
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitWindows collection being removed
     *
     * @return IrhpPermitStock
     */
    public function removeIrhpPermitWindows($irhpPermitWindows)
    {
        if ($this->irhpPermitWindows->contains($irhpPermitWindows)) {
            $this->irhpPermitWindows->removeElement($irhpPermitWindows);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
