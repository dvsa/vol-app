<?php

namespace Dvsa\Olcs\Api\Entity\EnforcementArea;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PostcodeEnforcementArea Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="postcode_enforcement_area",
 *    indexes={
 *        @ORM\Index(name="ix_postcode_enforcement_area_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_postcode_enforcement_area_enforcement_area_id",
     *     columns={"enforcement_area_id"}),
 *        @ORM\Index(name="ix_postcode_enforcement_area_last_modified_by",
     *     columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_postcode_enforcement_area_enforcement_area_id_postcode_id",
     *     columns={"enforcement_area_id","postcode_id"})
 *    }
 * )
 */
abstract class AbstractPostcodeEnforcementArea implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Enforcement area
     *
     * @var \Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea",
     *     fetch="LAZY"
     * )
     * @ORM\JoinColumn(name="enforcement_area_id", referencedColumnName="id", nullable=false)
     */
    protected $enforcementArea;

    /**
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

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
     * Postcode id
     *
     * @var string
     *
     * @ORM\Column(type="string", name="postcode_id", length=8, nullable=false)
     */
    protected $postcodeId;

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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the enforcement area
     *
     * @param \Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea $enforcementArea entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea
     */
    public function getEnforcementArea()
    {
        return $this->enforcementArea;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
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
     * @return string
     */
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
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
