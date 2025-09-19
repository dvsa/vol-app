<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\ContactDetails;

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
 * AbstractCountry Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="country",
 *    indexes={
 *        @ORM\Index(name="ix_country_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_country_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractCountry implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="id", length=2, nullable=false)
     */
    protected $id = '';

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
     * Country desc
     *
     * @var string
     *
     * @ORM\Column(type="string", name="country_desc", length=50, nullable=true)
     */
    protected $countryDesc;

    /**
     * Is EU member. Affects transit rules and EU permits
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_member_state", nullable=false, options={"default": 0})
     */
    protected $isMemberState = 0;

    /**
     * Is permit state
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_permit_state", nullable=false, options={"default": 0})
     */
    protected $isPermitState = 0;

    /**
     * Is ecmt state
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_ecmt_state", nullable=true, options={"default": 0})
     */
    protected $isEcmtState = 0;

    /**
     * Is eea state
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_eea_state", nullable=false, options={"default": 0})
     */
    protected $isEeaState = 0;

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
     * IrhpPermitStocks
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock", mappedBy="country")
     */
    protected $irhpPermitStocks;

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
        $this->irhpPermitStocks = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param string $id new value being set
     *
     * @return Country
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return string     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Country
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
     * @return Country
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
     * Set the country desc
     *
     * @param string $countryDesc new value being set
     *
     * @return Country
     */
    public function setCountryDesc($countryDesc)
    {
        $this->countryDesc = $countryDesc;

        return $this;
    }

    /**
     * Get the country desc
     *
     * @return string     */
    public function getCountryDesc()
    {
        return $this->countryDesc;
    }

    /**
     * Set the is member state
     *
     * @param string $isMemberState new value being set
     *
     * @return Country
     */
    public function setIsMemberState($isMemberState)
    {
        $this->isMemberState = $isMemberState;

        return $this;
    }

    /**
     * Get the is member state
     *
     * @return string     */
    public function getIsMemberState()
    {
        return $this->isMemberState;
    }

    /**
     * Set the is permit state
     *
     * @param bool $isPermitState new value being set
     *
     * @return Country
     */
    public function setIsPermitState($isPermitState)
    {
        $this->isPermitState = $isPermitState;

        return $this;
    }

    /**
     * Get the is permit state
     *
     * @return bool     */
    public function getIsPermitState()
    {
        return $this->isPermitState;
    }

    /**
     * Set the is ecmt state
     *
     * @param bool $isEcmtState new value being set
     *
     * @return Country
     */
    public function setIsEcmtState($isEcmtState)
    {
        $this->isEcmtState = $isEcmtState;

        return $this;
    }

    /**
     * Get the is ecmt state
     *
     * @return bool     */
    public function getIsEcmtState()
    {
        return $this->isEcmtState;
    }

    /**
     * Set the is eea state
     *
     * @param bool $isEeaState new value being set
     *
     * @return Country
     */
    public function setIsEeaState($isEeaState)
    {
        $this->isEeaState = $isEeaState;

        return $this;
    }

    /**
     * Get the is eea state
     *
     * @return bool     */
    public function getIsEeaState()
    {
        return $this->isEeaState;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Country
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
     * Set the irhp permit stocks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitStocks collection being set as the value
     *
     * @return Country
     */
    public function setIrhpPermitStocks($irhpPermitStocks)
    {
        $this->irhpPermitStocks = $irhpPermitStocks;

        return $this;
    }

    /**
     * Get the irhp permit stocks
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitStocks()
    {
        return $this->irhpPermitStocks;
    }

    /**
     * Add a irhp permit stocks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitStocks collection being added
     *
     * @return Country
     */
    public function addIrhpPermitStocks($irhpPermitStocks)
    {
        if ($irhpPermitStocks instanceof ArrayCollection) {
            $this->irhpPermitStocks = new ArrayCollection(
                array_merge(
                    $this->irhpPermitStocks->toArray(),
                    $irhpPermitStocks->toArray()
                )
            );
        } elseif (!$this->irhpPermitStocks->contains($irhpPermitStocks)) {
            $this->irhpPermitStocks->add($irhpPermitStocks);
        }

        return $this;
    }

    /**
     * Remove a irhp permit stocks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitStocks collection being removed
     *
     * @return Country
     */
    public function removeIrhpPermitStocks($irhpPermitStocks)
    {
        if ($this->irhpPermitStocks->contains($irhpPermitStocks)) {
            $this->irhpPermitStocks->removeElement($irhpPermitStocks);
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