<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\EnforcementArea;

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
 * AbstractPostcodeEnforcementArea Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="postcode_enforcement_area",
 *    indexes={
 *        @ORM\Index(name="ix_postcode_enforcement_area_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_postcode_enforcement_area_enforcement_area_id", columns={"enforcement_area_id"}),
 *        @ORM\Index(name="ix_postcode_enforcement_area_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="uk_postcode_enforcement_area_enforcement_area_id_postcode_id", columns={"enforcement_area_id", "postcode_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_postcode_enforcement_area_enforcement_area_id_postcode_id", columns={"enforcement_area_id", "postcode_id"})
 *    }
 * )
 */
abstract class AbstractPostcodeEnforcementArea implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to enforcement_area
     *
     * @var \Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea", fetch="LAZY")
     * @ORM\JoinColumn(name="enforcement_area_id", referencedColumnName="id")
     */
    protected $enforcementArea;

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
     * Postcode in the area.  Just the first part of the postcode.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="postcode_id", length=8, nullable=false)
     */
    protected $postcodeId = '';

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
     * @return PostcodeEnforcementArea
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
     * Set the enforcement area
     *
     * @param \Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea $enforcementArea new value being set
     *
     * @return PostcodeEnforcementArea
     */
    public function setEnforcementArea($enforcementArea)
    {
        $this->enforcementArea = $enforcementArea;

        return $this;
    }

    /**
     * Get the enforcement area
     *
     * @return \Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea     */
    public function getEnforcementArea()
    {
        return $this->enforcementArea;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return PostcodeEnforcementArea
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
     * @return PostcodeEnforcementArea
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
     * Set the postcode id
     *
     * @param string $postcodeId new value being set
     *
     * @return PostcodeEnforcementArea
     */
    public function setPostcodeId($postcodeId)
    {
        $this->postcodeId = $postcodeId;

        return $this;
    }

    /**
     * Get the postcode id
     *
     * @return string     */
    public function getPostcodeId()
    {
        return $this->postcodeId;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return PostcodeEnforcementArea
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
