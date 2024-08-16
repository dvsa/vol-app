<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SiCategoryType Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="si_category_type",
 *    indexes={
 *        @ORM\Index(name="ix_si_category_type_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_category_type_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_si_category_type_si_category_id", columns={"si_category_id"})
 *    }
 * )
 */
abstract class AbstractSiCategoryType implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

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
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=500, nullable=true)
     */
    protected $description;

    /**
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="smallint", name="id")
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
     * Si category
     *
     * @var \Dvsa\Olcs\Api\Entity\Si\SiCategory
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Si\SiCategory", fetch="LAZY")
     * @ORM\JoinColumn(name="si_category_id", referencedColumnName="id", nullable=false)
     */
    protected $siCategory;

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
     * @return SiCategoryType
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
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return SiCategoryType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return SiCategoryType
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
     * @return SiCategoryType
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
     * Set the si category
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\SiCategory $siCategory entity being set as the value
     *
     * @return SiCategoryType
     */
    public function setSiCategory($siCategory)
    {
        $this->siCategory = $siCategory;

        return $this;
    }

    /**
     * Get the si category
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\SiCategory
     */
    public function getSiCategory()
    {
        return $this->siCategory;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SiCategoryType
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
