<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

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
 * AbstractLetterType Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_type",
 *    indexes={
 *        @ORM\Index(name="ix_letter_type_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_letter_type_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_type_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_letter_type_letter_test_data_id", columns={"letter_test_data_id"}),
 *        @ORM\Index(name="ix_letter_type_master_template_id", columns={"master_template_id"}),
 *        @ORM\Index(name="ix_letter_type_sub_category_id", columns={"sub_category_id"})
 *    }
 * )
 */
abstract class AbstractLetterType implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * MasterTemplate
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\MasterTemplate
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\MasterTemplate", fetch="LAZY")
     * @ORM\JoinColumn(name="master_template_id", referencedColumnName="id", nullable=true)
     */
    protected $masterTemplate;

    /**
     * LetterTestData
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterTestData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterTestData", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_test_data_id", referencedColumnName="id", nullable=true)
     */
    protected $letterTestData;

    /**
     * Category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\Category
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\Category", fetch="LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true)
     */
    protected $category;

    /**
     * SubCategory
     *
     * @var \Dvsa\Olcs\Api\Entity\System\SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\SubCategory", fetch="LAZY")
     * @ORM\JoinColumn(name="sub_category_id", referencedColumnName="id", nullable=true)
     */
    protected $subCategory;

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
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=255, nullable=false)
     */
    protected $name = '';

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     */
    protected $description;

    /**
     * Is active
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_active", nullable=false, options={"default": 1})
     */
    protected $isActive = 1;

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
     * @return LetterType
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
     * Set the master template
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\MasterTemplate $masterTemplate new value being set
     *
     * @return LetterType
     */
    public function setMasterTemplate($masterTemplate)
    {
        $this->masterTemplate = $masterTemplate;

        return $this;
    }

    /**
     * Get the master template
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\MasterTemplate     */
    public function getMasterTemplate()
    {
        return $this->masterTemplate;
    }

    /**
     * Set the letter test data
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterTestData $letterTestData new value being set
     *
     * @return LetterType
     */
    public function setLetterTestData($letterTestData)
    {
        $this->letterTestData = $letterTestData;

        return $this;
    }

    /**
     * Get the letter test data
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterTestData     */
    public function getLetterTestData()
    {
        return $this->letterTestData;
    }

    /**
     * Set the category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category $category new value being set
     *
     * @return LetterType
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\Category     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the sub category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\SubCategory $subCategory new value being set
     *
     * @return LetterType
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get the sub category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\SubCategory     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LetterType
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
     * @return LetterType
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
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return LetterType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return LetterType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the is active
     *
     * @param bool $isActive new value being set
     *
     * @return LetterType
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get the is active
     *
     * @return bool     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LetterType
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
