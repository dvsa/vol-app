<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

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
 * AbstractSubCategory Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="sub_category",
 *    indexes={
 *        @ORM\Index(name="ix_sub_category_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_sub_category_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_sub_category_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractSubCategory implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    protected $id = 0;

    /**
     * Foreign Key to category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\Category
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\Category", fetch="LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

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
     * e.g. GV79 Form is a sub cat of application category
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sub_category_name", length=64, nullable=false)
     */
    protected $subCategoryName = '';

    /**
     * Category used for scanning documents
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_scan", nullable=false, options={"default": 0})
     */
    protected $isScan = 0;

    /**
     * Is a valid document category
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_doc", nullable=false, options={"default": 0})
     */
    protected $isDoc = 0;

    /**
     * Is a valid task category
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_task", nullable=false, options={"default": 0})
     */
    protected $isTask = 0;

    /**
     * User can enter freetext description - applied to task etc when creating.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_free_text", nullable=false, options={"default": 0})
     */
    protected $isFreeText = 0;

    /**
     * Is messaging
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_messaging", nullable=false, options={"default": 0})
     */
    protected $isMessaging = 0;

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
     * @return SubCategory
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
     * Set the category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category $category new value being set
     *
     * @return SubCategory
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return SubCategory
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
     * @return SubCategory
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
     * Set the sub category name
     *
     * @param string $subCategoryName new value being set
     *
     * @return SubCategory
     */
    public function setSubCategoryName($subCategoryName)
    {
        $this->subCategoryName = $subCategoryName;

        return $this;
    }

    /**
     * Get the sub category name
     *
     * @return string     */
    public function getSubCategoryName()
    {
        return $this->subCategoryName;
    }

    /**
     * Set the is scan
     *
     * @param bool $isScan new value being set
     *
     * @return SubCategory
     */
    public function setIsScan($isScan)
    {
        $this->isScan = $isScan;

        return $this;
    }

    /**
     * Get the is scan
     *
     * @return bool     */
    public function getIsScan()
    {
        return $this->isScan;
    }

    /**
     * Set the is doc
     *
     * @param bool $isDoc new value being set
     *
     * @return SubCategory
     */
    public function setIsDoc($isDoc)
    {
        $this->isDoc = $isDoc;

        return $this;
    }

    /**
     * Get the is doc
     *
     * @return bool     */
    public function getIsDoc()
    {
        return $this->isDoc;
    }

    /**
     * Set the is task
     *
     * @param bool $isTask new value being set
     *
     * @return SubCategory
     */
    public function setIsTask($isTask)
    {
        $this->isTask = $isTask;

        return $this;
    }

    /**
     * Get the is task
     *
     * @return bool     */
    public function getIsTask()
    {
        return $this->isTask;
    }

    /**
     * Set the is free text
     *
     * @param bool $isFreeText new value being set
     *
     * @return SubCategory
     */
    public function setIsFreeText($isFreeText)
    {
        $this->isFreeText = $isFreeText;

        return $this;
    }

    /**
     * Get the is free text
     *
     * @return bool     */
    public function getIsFreeText()
    {
        return $this->isFreeText;
    }

    /**
     * Set the is messaging
     *
     * @param bool $isMessaging new value being set
     *
     * @return SubCategory
     */
    public function setIsMessaging($isMessaging)
    {
        $this->isMessaging = $isMessaging;

        return $this;
    }

    /**
     * Get the is messaging
     *
     * @return bool     */
    public function getIsMessaging()
    {
        return $this->isMessaging;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SubCategory
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
