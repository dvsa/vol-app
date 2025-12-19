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
 * AbstractIrhpPermitRange Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irhp_permit_range",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_permit_range_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_permit_range_emissions_category_ref_data_id", columns={"emissions_category"}),
 *        @ORM\Index(name="fk_irhp_permit_range_journey_ref_data_id", columns={"journey"}),
 *        @ORM\Index(name="fk_irhp_permit_range_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_irhp_permit_stock_ranges_irhp_permit_stocks1_idx", columns={"irhp_permit_stock_id"}),
 *        @ORM\Index(name="uniqueRange", columns={"irhp_permit_stock_id", "prefix", "from_no", "to_no"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uniqueRange", columns={"irhp_permit_stock_id", "prefix", "from_no", "to_no"})
 *    }
 * )
 */
abstract class AbstractIrhpPermitRange implements BundleSerializableInterface, JsonSerializable
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
     * IrhpPermitStock
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_permit_stock_id", referencedColumnName="id")
     */
    protected $irhpPermitStock;

    /**
     * EmissionsCategory
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="emissions_category", referencedColumnName="id", nullable=true)
     */
    protected $emissionsCategory;

    /**
     * Journey
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="journey", referencedColumnName="id", nullable=true)
     */
    protected $journey;

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
     * Prefix
     *
     * @var string
     *
     * @ORM\Column(type="string", name="prefix", length=45, nullable=true)
     */
    protected $prefix;

    /**
     * From no
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="from_no", nullable=true)
     */
    protected $fromNo;

    /**
     * To no
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="to_no", nullable=true)
     */
    protected $toNo;

    /**
     * Cabotage
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="cabotage", nullable=true)
     */
    protected $cabotage;

    /**
     * Ss reserve
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="ss_reserve", nullable=true)
     */
    protected $ssReserve;

    /**
     * Lost replacement
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="lost_replacement", nullable=true)
     */
    protected $lostReplacement;

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
     * IrhpPermitRangeAttributes
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", inversedBy="irhpPermitRanges", fetch="LAZY")
     * @ORM\JoinTable(name="irhp_permit_range_attribute",
     *     joinColumns={
     *         @ORM\JoinColumn(name="irhp_permit_range_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="irhp_permit_range_attribute_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $irhpPermitRangeAttributes;

    /**
     * Countrys
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\Country", inversedBy="irhpPermitStockRanges", fetch="LAZY")
     * @ORM\JoinTable(name="irhp_permit_range_country",
     *     joinColumns={
     *         @ORM\JoinColumn(name="irhp_permit_stock_range_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $countrys;

    /**
     * IrhpCandidatePermits
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit", mappedBy="irhpPermitRange")
     */
    protected $irhpCandidatePermits;

    /**
     * IrhpPermits
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermit", mappedBy="irhpPermitRange")
     */
    protected $irhpPermits;

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
        $this->irhpPermitRangeAttributes = new ArrayCollection();
        $this->countrys = new ArrayCollection();
        $this->irhpCandidatePermits = new ArrayCollection();
        $this->irhpPermits = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return IrhpPermitRange
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
     * Set the irhp permit stock
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock $irhpPermitStock new value being set
     *
     * @return IrhpPermitRange
     */
    public function setIrhpPermitStock($irhpPermitStock)
    {
        $this->irhpPermitStock = $irhpPermitStock;

        return $this;
    }

    /**
     * Get the irhp permit stock
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock     */
    public function getIrhpPermitStock()
    {
        return $this->irhpPermitStock;
    }

    /**
     * Set the emissions category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $emissionsCategory new value being set
     *
     * @return IrhpPermitRange
     */
    public function setEmissionsCategory($emissionsCategory)
    {
        $this->emissionsCategory = $emissionsCategory;

        return $this;
    }

    /**
     * Get the emissions category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getEmissionsCategory()
    {
        return $this->emissionsCategory;
    }

    /**
     * Set the journey
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $journey new value being set
     *
     * @return IrhpPermitRange
     */
    public function setJourney($journey)
    {
        $this->journey = $journey;

        return $this;
    }

    /**
     * Get the journey
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getJourney()
    {
        return $this->journey;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return IrhpPermitRange
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
     * @return IrhpPermitRange
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
     * Set the prefix
     *
     * @param string $prefix new value being set
     *
     * @return IrhpPermitRange
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the prefix
     *
     * @return string     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the from no
     *
     * @param int $fromNo new value being set
     *
     * @return IrhpPermitRange
     */
    public function setFromNo($fromNo)
    {
        $this->fromNo = $fromNo;

        return $this;
    }

    /**
     * Get the from no
     *
     * @return int     */
    public function getFromNo()
    {
        return $this->fromNo;
    }

    /**
     * Set the to no
     *
     * @param int $toNo new value being set
     *
     * @return IrhpPermitRange
     */
    public function setToNo($toNo)
    {
        $this->toNo = $toNo;

        return $this;
    }

    /**
     * Get the to no
     *
     * @return int     */
    public function getToNo()
    {
        return $this->toNo;
    }

    /**
     * Set the cabotage
     *
     * @param bool $cabotage new value being set
     *
     * @return IrhpPermitRange
     */
    public function setCabotage($cabotage)
    {
        $this->cabotage = $cabotage;

        return $this;
    }

    /**
     * Get the cabotage
     *
     * @return bool     */
    public function getCabotage()
    {
        return $this->cabotage;
    }

    /**
     * Set the ss reserve
     *
     * @param bool $ssReserve new value being set
     *
     * @return IrhpPermitRange
     */
    public function setSsReserve($ssReserve)
    {
        $this->ssReserve = $ssReserve;

        return $this;
    }

    /**
     * Get the ss reserve
     *
     * @return bool     */
    public function getSsReserve()
    {
        return $this->ssReserve;
    }

    /**
     * Set the lost replacement
     *
     * @param bool $lostReplacement new value being set
     *
     * @return IrhpPermitRange
     */
    public function setLostReplacement($lostReplacement)
    {
        $this->lostReplacement = $lostReplacement;

        return $this;
    }

    /**
     * Get the lost replacement
     *
     * @return bool     */
    public function getLostReplacement()
    {
        return $this->lostReplacement;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrhpPermitRange
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
     * Set the irhp permit range attributes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRangeAttributes collection being set as the value
     *
     * @return IrhpPermitRange
     */
    public function setIrhpPermitRangeAttributes($irhpPermitRangeAttributes)
    {
        $this->irhpPermitRangeAttributes = $irhpPermitRangeAttributes;

        return $this;
    }

    /**
     * Get the irhp permit range attributes
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitRangeAttributes()
    {
        return $this->irhpPermitRangeAttributes;
    }

    /**
     * Add a irhp permit range attributes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitRangeAttributes collection being added
     *
     * @return IrhpPermitRange
     */
    public function addIrhpPermitRangeAttributes($irhpPermitRangeAttributes)
    {
        if ($irhpPermitRangeAttributes instanceof ArrayCollection) {
            $this->irhpPermitRangeAttributes = new ArrayCollection(
                array_merge(
                    $this->irhpPermitRangeAttributes->toArray(),
                    $irhpPermitRangeAttributes->toArray()
                )
            );
        } elseif (!$this->irhpPermitRangeAttributes->contains($irhpPermitRangeAttributes)) {
            $this->irhpPermitRangeAttributes->add($irhpPermitRangeAttributes);
        }

        return $this;
    }

    /**
     * Remove a irhp permit range attributes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRangeAttributes collection being removed
     *
     * @return IrhpPermitRange
     */
    public function removeIrhpPermitRangeAttributes($irhpPermitRangeAttributes)
    {
        if ($this->irhpPermitRangeAttributes->contains($irhpPermitRangeAttributes)) {
            $this->irhpPermitRangeAttributes->removeElement($irhpPermitRangeAttributes);
        }

        return $this;
    }

    /**
     * Set the countrys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $countrys collection being set as the value
     *
     * @return IrhpPermitRange
     */
    public function setCountrys($countrys)
    {
        $this->countrys = $countrys;

        return $this;
    }

    /**
     * Get the countrys
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCountrys()
    {
        return $this->countrys;
    }

    /**
     * Add a countrys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $countrys collection being added
     *
     * @return IrhpPermitRange
     */
    public function addCountrys($countrys)
    {
        if ($countrys instanceof ArrayCollection) {
            $this->countrys = new ArrayCollection(
                array_merge(
                    $this->countrys->toArray(),
                    $countrys->toArray()
                )
            );
        } elseif (!$this->countrys->contains($countrys)) {
            $this->countrys->add($countrys);
        }

        return $this;
    }

    /**
     * Remove a countrys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $countrys collection being removed
     *
     * @return IrhpPermitRange
     */
    public function removeCountrys($countrys)
    {
        if ($this->countrys->contains($countrys)) {
            $this->countrys->removeElement($countrys);
        }

        return $this;
    }

    /**
     * Set the irhp candidate permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpCandidatePermits collection being set as the value
     *
     * @return IrhpPermitRange
     */
    public function setIrhpCandidatePermits($irhpCandidatePermits)
    {
        $this->irhpCandidatePermits = $irhpCandidatePermits;

        return $this;
    }

    /**
     * Get the irhp candidate permits
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpCandidatePermits()
    {
        return $this->irhpCandidatePermits;
    }

    /**
     * Add a irhp candidate permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpCandidatePermits collection being added
     *
     * @return IrhpPermitRange
     */
    public function addIrhpCandidatePermits($irhpCandidatePermits)
    {
        if ($irhpCandidatePermits instanceof ArrayCollection) {
            $this->irhpCandidatePermits = new ArrayCollection(
                array_merge(
                    $this->irhpCandidatePermits->toArray(),
                    $irhpCandidatePermits->toArray()
                )
            );
        } elseif (!$this->irhpCandidatePermits->contains($irhpCandidatePermits)) {
            $this->irhpCandidatePermits->add($irhpCandidatePermits);
        }

        return $this;
    }

    /**
     * Remove a irhp candidate permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpCandidatePermits collection being removed
     *
     * @return IrhpPermitRange
     */
    public function removeIrhpCandidatePermits($irhpCandidatePermits)
    {
        if ($this->irhpCandidatePermits->contains($irhpCandidatePermits)) {
            $this->irhpCandidatePermits->removeElement($irhpCandidatePermits);
        }

        return $this;
    }

    /**
     * Set the irhp permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermits collection being set as the value
     *
     * @return IrhpPermitRange
     */
    public function setIrhpPermits($irhpPermits)
    {
        $this->irhpPermits = $irhpPermits;

        return $this;
    }

    /**
     * Get the irhp permits
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermits()
    {
        return $this->irhpPermits;
    }

    /**
     * Add a irhp permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermits collection being added
     *
     * @return IrhpPermitRange
     */
    public function addIrhpPermits($irhpPermits)
    {
        if ($irhpPermits instanceof ArrayCollection) {
            $this->irhpPermits = new ArrayCollection(
                array_merge(
                    $this->irhpPermits->toArray(),
                    $irhpPermits->toArray()
                )
            );
        } elseif (!$this->irhpPermits->contains($irhpPermits)) {
            $this->irhpPermits->add($irhpPermits);
        }

        return $this;
    }

    /**
     * Remove a irhp permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermits collection being removed
     *
     * @return IrhpPermitRange
     */
    public function removeIrhpPermits($irhpPermits)
    {
        if ($this->irhpPermits->contains($irhpPermits)) {
            $this->irhpPermits->removeElement($irhpPermits);
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