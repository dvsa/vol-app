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
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractCommunityLic Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="community_lic",
 *    indexes={
 *        @ORM\Index(name="ix_community_lic_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_community_lic_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_community_lic_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_community_lic_status", columns={"status"}),
 *        @ORM\Index(name="uk_community_lic_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_community_lic_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractCommunityLic implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id")
     */
    protected $licence;

    /**
     * annulled, cns, expired, pending, returned, revoked, surrender, suspended, valid, void, withdrawn
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id")
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
     * The date the licence expired.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="expired_date", nullable=true)
     */
    protected $expiredDate;

    /**
     * Activation date of com licence.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="specified_date", nullable=true)
     */
    protected $specifiedDate;

    /**
     * The date the community licence will expire. Typically 5 years after specified date.  Generally less for an interim licence.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="licence_expired_date", nullable=true)
     */
    protected $licenceExpiredDate;

    /**
     * Issue 0 is the office copy. 0 is the licence, all others are refered to as certified copies.
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="issue_no", nullable=true)
     */
    protected $issueNo;

    /**
     * Business ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="serial_no", nullable=true)
     */
    protected $serialNo;

    /**
     * UKGB or UKNI
     *
     * @var string
     *
     * @ORM\Column(type="string", name="serial_no_prefix", length=4, nullable=true)
     */
    protected $serialNoPrefix;

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
     * CommunityLicSuspensions
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspension", mappedBy="communityLic")
     */
    protected $communityLicSuspensions;

    /**
     * CommunityLicWithdrawals
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawal", mappedBy="communityLic")
     */
    protected $communityLicWithdrawals;

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
        $this->communityLicSuspensions = new ArrayCollection();
        $this->communityLicWithdrawals = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return CommunityLic
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
     * @return CommunityLic
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
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status new value being set
     *
     * @return CommunityLic
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
     * @return CommunityLic
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
     * @return CommunityLic
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
     * Set the expired date
     *
     * @param \DateTime $expiredDate new value being set
     *
     * @return CommunityLic
     */
    public function setExpiredDate($expiredDate)
    {
        $this->expiredDate = $expiredDate;

        return $this;
    }

    /**
     * Get the expired date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getExpiredDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->expiredDate);
        }

        return $this->expiredDate;
    }

    /**
     * Set the specified date
     *
     * @param \DateTime $specifiedDate new value being set
     *
     * @return CommunityLic
     */
    public function setSpecifiedDate($specifiedDate)
    {
        $this->specifiedDate = $specifiedDate;

        return $this;
    }

    /**
     * Get the specified date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getSpecifiedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->specifiedDate);
        }

        return $this->specifiedDate;
    }

    /**
     * Set the licence expired date
     *
     * @param \DateTime $licenceExpiredDate new value being set
     *
     * @return CommunityLic
     */
    public function setLicenceExpiredDate($licenceExpiredDate)
    {
        $this->licenceExpiredDate = $licenceExpiredDate;

        return $this;
    }

    /**
     * Get the licence expired date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getLicenceExpiredDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->licenceExpiredDate);
        }

        return $this->licenceExpiredDate;
    }

    /**
     * Set the issue no
     *
     * @param int $issueNo new value being set
     *
     * @return CommunityLic
     */
    public function setIssueNo($issueNo)
    {
        $this->issueNo = $issueNo;

        return $this;
    }

    /**
     * Get the issue no
     *
     * @return int     */
    public function getIssueNo()
    {
        return $this->issueNo;
    }

    /**
     * Set the serial no
     *
     * @param int $serialNo new value being set
     *
     * @return CommunityLic
     */
    public function setSerialNo($serialNo)
    {
        $this->serialNo = $serialNo;

        return $this;
    }

    /**
     * Get the serial no
     *
     * @return int     */
    public function getSerialNo()
    {
        return $this->serialNo;
    }

    /**
     * Set the serial no prefix
     *
     * @param string $serialNoPrefix new value being set
     *
     * @return CommunityLic
     */
    public function setSerialNoPrefix($serialNoPrefix)
    {
        $this->serialNoPrefix = $serialNoPrefix;

        return $this;
    }

    /**
     * Get the serial no prefix
     *
     * @return string     */
    public function getSerialNoPrefix()
    {
        return $this->serialNoPrefix;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return CommunityLic
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
     * @return CommunityLic
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
     * Set the community lic suspensions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $communityLicSuspensions collection being set as the value
     *
     * @return CommunityLic
     */
    public function setCommunityLicSuspensions($communityLicSuspensions)
    {
        $this->communityLicSuspensions = $communityLicSuspensions;

        return $this;
    }

    /**
     * Get the community lic suspensions
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCommunityLicSuspensions()
    {
        return $this->communityLicSuspensions;
    }

    /**
     * Add a community lic suspensions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $communityLicSuspensions collection being added
     *
     * @return CommunityLic
     */
    public function addCommunityLicSuspensions($communityLicSuspensions)
    {
        if ($communityLicSuspensions instanceof ArrayCollection) {
            $this->communityLicSuspensions = new ArrayCollection(
                array_merge(
                    $this->communityLicSuspensions->toArray(),
                    $communityLicSuspensions->toArray()
                )
            );
        } elseif (!$this->communityLicSuspensions->contains($communityLicSuspensions)) {
            $this->communityLicSuspensions->add($communityLicSuspensions);
        }

        return $this;
    }

    /**
     * Remove a community lic suspensions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $communityLicSuspensions collection being removed
     *
     * @return CommunityLic
     */
    public function removeCommunityLicSuspensions($communityLicSuspensions)
    {
        if ($this->communityLicSuspensions->contains($communityLicSuspensions)) {
            $this->communityLicSuspensions->removeElement($communityLicSuspensions);
        }

        return $this;
    }

    /**
     * Set the community lic withdrawals
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $communityLicWithdrawals collection being set as the value
     *
     * @return CommunityLic
     */
    public function setCommunityLicWithdrawals($communityLicWithdrawals)
    {
        $this->communityLicWithdrawals = $communityLicWithdrawals;

        return $this;
    }

    /**
     * Get the community lic withdrawals
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCommunityLicWithdrawals()
    {
        return $this->communityLicWithdrawals;
    }

    /**
     * Add a community lic withdrawals
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $communityLicWithdrawals collection being added
     *
     * @return CommunityLic
     */
    public function addCommunityLicWithdrawals($communityLicWithdrawals)
    {
        if ($communityLicWithdrawals instanceof ArrayCollection) {
            $this->communityLicWithdrawals = new ArrayCollection(
                array_merge(
                    $this->communityLicWithdrawals->toArray(),
                    $communityLicWithdrawals->toArray()
                )
            );
        } elseif (!$this->communityLicWithdrawals->contains($communityLicWithdrawals)) {
            $this->communityLicWithdrawals->add($communityLicWithdrawals);
        }

        return $this;
    }

    /**
     * Remove a community lic withdrawals
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $communityLicWithdrawals collection being removed
     *
     * @return CommunityLic
     */
    public function removeCommunityLicWithdrawals($communityLicWithdrawals)
    {
        if ($this->communityLicWithdrawals->contains($communityLicWithdrawals)) {
            $this->communityLicWithdrawals->removeElement($communityLicWithdrawals);
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
