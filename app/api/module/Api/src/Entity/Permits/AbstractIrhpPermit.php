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
 * AbstractIrhpPermit Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irhp_permit",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_permit_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_permit_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_irhp_permit_replaces_id_irhp_permit_id", columns={"replaces_id"}),
 *        @ORM\Index(name="fk_irhp_permit_status_ref_data_id", columns={"status"}),
 *        @ORM\Index(name="fk_irhp_permits_irhp_candidate_permit1_idx", columns={"irhp_candidate_permit_id"}),
 *        @ORM\Index(name="fk_irhp_permits_irhp_permit_application1_idx", columns={"irhp_permit_application_id"}),
 *        @ORM\Index(name="fk_irhp_permits_irhp_permit_range_idx", columns={"irhp_permit_range_id"})
 *    }
 * )
 */
abstract class AbstractIrhpPermit implements BundleSerializableInterface, JsonSerializable
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
     * Replaces
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermit
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermit", fetch="LAZY")
     * @ORM\JoinColumn(name="replaces_id", referencedColumnName="id", nullable=true)
     */
    protected $replaces;

    /**
     * IrhpPermitRange
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_permit_range_id", referencedColumnName="id")
     */
    protected $irhpPermitRange;

    /**
     * IrhpPermitApplication
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_permit_application_id", referencedColumnName="id")
     */
    protected $irhpPermitApplication;

    /**
     * IrhpCandidatePermit
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_candidate_permit_id", referencedColumnName="id", nullable=true)
     */
    protected $irhpCandidatePermit;

    /**
     * Status
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
     * Permit number
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="permit_number", nullable=true)
     */
    protected $permitNumber;

    /**
     * Issue date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="issue_date", nullable=true)
     */
    protected $issueDate;

    /**
     * Expiry date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="expiry_date", nullable=true)
     */
    protected $expiryDate;

    /**
     * Permit properties
     *
     * @var string
     *
     * @ORM\Column(type="text", name="permit_properties", nullable=true)
     */
    protected $permitProperties;

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
     * @return IrhpPermit
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
     * Set the replaces
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermit $replaces new value being set
     *
     * @return IrhpPermit
     */
    public function setReplaces($replaces)
    {
        $this->replaces = $replaces;

        return $this;
    }

    /**
     * Get the replaces
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermit     */
    public function getReplaces()
    {
        return $this->replaces;
    }

    /**
     * Set the irhp permit range
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange $irhpPermitRange new value being set
     *
     * @return IrhpPermit
     */
    public function setIrhpPermitRange($irhpPermitRange)
    {
        $this->irhpPermitRange = $irhpPermitRange;

        return $this;
    }

    /**
     * Get the irhp permit range
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange     */
    public function getIrhpPermitRange()
    {
        return $this->irhpPermitRange;
    }

    /**
     * Set the irhp permit application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication $irhpPermitApplication new value being set
     *
     * @return IrhpPermit
     */
    public function setIrhpPermitApplication($irhpPermitApplication)
    {
        $this->irhpPermitApplication = $irhpPermitApplication;

        return $this;
    }

    /**
     * Get the irhp permit application
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication     */
    public function getIrhpPermitApplication()
    {
        return $this->irhpPermitApplication;
    }

    /**
     * Set the irhp candidate permit
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit $irhpCandidatePermit new value being set
     *
     * @return IrhpPermit
     */
    public function setIrhpCandidatePermit($irhpCandidatePermit)
    {
        $this->irhpCandidatePermit = $irhpCandidatePermit;

        return $this;
    }

    /**
     * Get the irhp candidate permit
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit     */
    public function getIrhpCandidatePermit()
    {
        return $this->irhpCandidatePermit;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status new value being set
     *
     * @return IrhpPermit
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
     * @return IrhpPermit
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
     * @return IrhpPermit
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
     * Set the permit number
     *
     * @param int $permitNumber new value being set
     *
     * @return IrhpPermit
     */
    public function setPermitNumber($permitNumber)
    {
        $this->permitNumber = $permitNumber;

        return $this;
    }

    /**
     * Get the permit number
     *
     * @return int     */
    public function getPermitNumber()
    {
        return $this->permitNumber;
    }

    /**
     * Set the issue date
     *
     * @param \DateTime $issueDate new value being set
     *
     * @return IrhpPermit
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    /**
     * Get the issue date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getIssueDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->issueDate);
        }

        return $this->issueDate;
    }

    /**
     * Set the expiry date
     *
     * @param \DateTime $expiryDate new value being set
     *
     * @return IrhpPermit
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get the expiry date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getExpiryDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->expiryDate);
        }

        return $this->expiryDate;
    }

    /**
     * Set the permit properties
     *
     * @param string $permitProperties new value being set
     *
     * @return IrhpPermit
     */
    public function setPermitProperties($permitProperties)
    {
        $this->permitProperties = $permitProperties;

        return $this;
    }

    /**
     * Get the permit properties
     *
     * @return string     */
    public function getPermitProperties()
    {
        return $this->permitProperties;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrhpPermit
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