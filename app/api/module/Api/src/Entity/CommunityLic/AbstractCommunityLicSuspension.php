<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\CommunityLic;

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
 * AbstractCommunityLicSuspension Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="community_lic_suspension",
 *    indexes={
 *        @ORM\Index(name="ix_community_lic_suspension_community_lic_id", columns={"community_lic_id"}),
 *        @ORM\Index(name="ix_community_lic_suspension_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_community_lic_suspension_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="uk_community_lic_suspension_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_community_lic_suspension_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractCommunityLicSuspension implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to community_lic
     *
     * @var \Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="community_lic_id", referencedColumnName="id")
     */
    protected $communityLic;

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
     * @ORM\Column(type="datetime", name="start_date", nullable=true)
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
     * Possibly not required. In legacy as part of batch job.
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_actioned", nullable=true, options={"default": 0})
     */
    protected $isActioned = 0;

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
     * CommunityLicSuspensionReasons
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspensionReason", mappedBy="communityLicSuspension")
     */
    protected $communityLicSuspensionReasons;

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
        $this->communityLicSuspensionReasons = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return CommunityLicSuspension
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
     * Set the community lic
     *
     * @param \Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic $communityLic new value being set
     *
     * @return CommunityLicSuspension
     */
    public function setCommunityLic($communityLic)
    {
        $this->communityLic = $communityLic;

        return $this;
    }

    /**
     * Get the community lic
     *
     * @return \Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic     */
    public function getCommunityLic()
    {
        return $this->communityLic;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return CommunityLicSuspension
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
     * @return CommunityLicSuspension
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
     * @return CommunityLicSuspension
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
     * @return CommunityLicSuspension
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
     * Set the is actioned
     *
     * @param string $isActioned new value being set
     *
     * @return CommunityLicSuspension
     */
    public function setIsActioned($isActioned)
    {
        $this->isActioned = $isActioned;

        return $this;
    }

    /**
     * Get the is actioned
     *
     * @return string     */
    public function getIsActioned()
    {
        return $this->isActioned;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return CommunityLicSuspension
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
     * @return CommunityLicSuspension
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
     * Set the community lic suspension reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $communityLicSuspensionReasons collection being set as the value
     *
     * @return CommunityLicSuspension
     */
    public function setCommunityLicSuspensionReasons($communityLicSuspensionReasons)
    {
        $this->communityLicSuspensionReasons = $communityLicSuspensionReasons;

        return $this;
    }

    /**
     * Get the community lic suspension reasons
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCommunityLicSuspensionReasons()
    {
        return $this->communityLicSuspensionReasons;
    }

    /**
     * Add a community lic suspension reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $communityLicSuspensionReasons collection being added
     *
     * @return CommunityLicSuspension
     */
    public function addCommunityLicSuspensionReasons($communityLicSuspensionReasons)
    {
        if ($communityLicSuspensionReasons instanceof ArrayCollection) {
            $this->communityLicSuspensionReasons = new ArrayCollection(
                array_merge(
                    $this->communityLicSuspensionReasons->toArray(),
                    $communityLicSuspensionReasons->toArray()
                )
            );
        } elseif (!$this->communityLicSuspensionReasons->contains($communityLicSuspensionReasons)) {
            $this->communityLicSuspensionReasons->add($communityLicSuspensionReasons);
        }

        return $this;
    }

    /**
     * Remove a community lic suspension reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $communityLicSuspensionReasons collection being removed
     *
     * @return CommunityLicSuspension
     */
    public function removeCommunityLicSuspensionReasons($communityLicSuspensionReasons)
    {
        if ($this->communityLicSuspensionReasons->contains($communityLicSuspensionReasons)) {
            $this->communityLicSuspensionReasons->removeElement($communityLicSuspensionReasons);
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
