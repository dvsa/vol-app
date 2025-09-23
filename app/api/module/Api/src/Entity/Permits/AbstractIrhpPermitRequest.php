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
 * AbstractIrhpPermitRequest Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irhp_permit_request",
 *    indexes={
 *        @ORM\Index(name="ix_irhp_permit_request_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irhp_permit_request_irhp_application_id", columns={"irhp_application_id"}),
 *        @ORM\Index(name="ix_irhp_permit_request_irhp_permit_application_id", columns={"irhp_permit_application_id"}),
 *        @ORM\Index(name="ix_irhp_permit_request_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractIrhpPermitRequest implements BundleSerializableInterface, JsonSerializable
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
     * IrhpApplication
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpApplication", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_application_id", referencedColumnName="id", nullable=true)
     */
    protected $irhpApplication;

    /**
     * IrhpPermitApplication
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_permit_application_id", referencedColumnName="id", nullable=true)
     */
    protected $irhpPermitApplication;

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
     * Permits required
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="permits_required", nullable=false)
     */
    protected $permitsRequired = 0;

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
     * IrhpPermitRequestAttributes
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", inversedBy="irhpPermitRequests", fetch="LAZY")
     * @ORM\JoinTable(name="irhp_permit_request_attribute",
     *     joinColumns={
     *         @ORM\JoinColumn(name="irhp_permit_request_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="irhp_permit_request_attribute_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $irhpPermitRequestAttributes;

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
        $this->irhpPermitRequestAttributes = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return IrhpPermitRequest
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
     * Set the irhp application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication $irhpApplication new value being set
     *
     * @return IrhpPermitRequest
     */
    public function setIrhpApplication($irhpApplication)
    {
        $this->irhpApplication = $irhpApplication;

        return $this;
    }

    /**
     * Get the irhp application
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication     */
    public function getIrhpApplication()
    {
        return $this->irhpApplication;
    }

    /**
     * Set the irhp permit application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication $irhpPermitApplication new value being set
     *
     * @return IrhpPermitRequest
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return IrhpPermitRequest
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
     * @return IrhpPermitRequest
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
     * Set the permits required
     *
     * @param int $permitsRequired new value being set
     *
     * @return IrhpPermitRequest
     */
    public function setPermitsRequired($permitsRequired)
    {
        $this->permitsRequired = $permitsRequired;

        return $this;
    }

    /**
     * Get the permits required
     *
     * @return int     */
    public function getPermitsRequired()
    {
        return $this->permitsRequired;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrhpPermitRequest
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
     * Set the irhp permit request attributes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRequestAttributes collection being set as the value
     *
     * @return IrhpPermitRequest
     */
    public function setIrhpPermitRequestAttributes($irhpPermitRequestAttributes)
    {
        $this->irhpPermitRequestAttributes = $irhpPermitRequestAttributes;

        return $this;
    }

    /**
     * Get the irhp permit request attributes
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitRequestAttributes()
    {
        return $this->irhpPermitRequestAttributes;
    }

    /**
     * Add a irhp permit request attributes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitRequestAttributes collection being added
     *
     * @return IrhpPermitRequest
     */
    public function addIrhpPermitRequestAttributes($irhpPermitRequestAttributes)
    {
        if ($irhpPermitRequestAttributes instanceof ArrayCollection) {
            $this->irhpPermitRequestAttributes = new ArrayCollection(
                array_merge(
                    $this->irhpPermitRequestAttributes->toArray(),
                    $irhpPermitRequestAttributes->toArray()
                )
            );
        } elseif (!$this->irhpPermitRequestAttributes->contains($irhpPermitRequestAttributes)) {
            $this->irhpPermitRequestAttributes->add($irhpPermitRequestAttributes);
        }

        return $this;
    }

    /**
     * Remove a irhp permit request attributes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRequestAttributes collection being removed
     *
     * @return IrhpPermitRequest
     */
    public function removeIrhpPermitRequestAttributes($irhpPermitRequestAttributes)
    {
        if ($this->irhpPermitRequestAttributes->contains($irhpPermitRequestAttributes)) {
            $this->irhpPermitRequestAttributes->removeElement($irhpPermitRequestAttributes);
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